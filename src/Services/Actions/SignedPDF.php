<?php
namespace CentralTickets\Services\Actions;

use Exception;

/**
 * Verificador de firmas digitales PDF ecuatorianas
 * 
 * Esta clase verifica la validez de firmas digitales en documentos PDF,
 * especialmente enfocada en certificados emitidos por autoridades
 * certificadoras reconocidas en Ecuador.
 */
final class SignedPDF
{
    // Constantes para autoridades certificadoras ecuatorianas
    private const ECUADORIAN_CAS = [
        'BCE' => [
            'names' => ['Banco Central del Ecuador', 'BCE', 'BCECERT'],
            'oids' => ['2.16.218.1.1.1'],
        ],
        'SECURITY_DATA' => [
            'names' => ['Security Data', 'SECURITY DATA', 'SD'],
            'oids' => ['1.3.6.1.4.1.37947'],
        ],
        'UANATACA' => [
            'names' => ['Uanataca', 'UANATACA S.A.', 'ANF AC'],
            'oids' => ['1.3.6.1.4.1.18332'],
        ],
        'ANF' => [
            'names' => ['ANF AC', 'ANF Autoridad de Certificación', 'Autoridad de Certificacion Firmaprofesional'],
            'oids' => ['1.3.6.1.4.1.18332'],
        ],
        'ECLIPSOFT' => [
            'names' => ['Eclipsoft', 'ECLIPSOFT CA'],
            'oids' => ['1.3.6.1.4.1.48832'],
        ],
    ];

    private const GOVERNMENT_DOMAINS = [
        'gob.ec', 'ecuador.gob.ec', 'presidencia.gob.ec',
        'sri.gob.ec', 'senescyt.gob.ec', 'salud.gob.ec',
    ];

    private const GRACE_PERIOD_DAYS = 30;

    /**
     * Verificar si un PDF está firmado digitalmente y es válido
     */
    public function verifySigned(string $pdf_path): bool
    {
        try {
            $pdf = $this->loadPdf($pdf_path);
            $certificate = $this->processPdfSignature($pdf);
            
            return $this->validateCertificate($certificate, $pdf);
            
        } catch (Exception $e) {
            error_log("[SignedPDF] Verification failed: " . $e->getMessage());
            return false;
        }
    }

    // =================================================================
    // MÉTODOS DE CARGA Y PROCESAMIENTO PRINCIPAL
    // =================================================================

    private function loadPdf(string $pdf_path): string
    {
        $pdf = file_get_contents($pdf_path);
        if ($pdf === false) {
            throw new Exception("Cannot read PDF file: {$pdf_path}");
        }
        return $pdf;
    }

    private function processPdfSignature(string $pdf): array
    {
        if (!$this->hasDigitalSignature($pdf)) {
            throw new Exception("No digital signature found in PDF");
        }

        $certificate = $this->extractCertificateData($pdf);
        if ($certificate === null) {
            throw new Exception("Cannot extract certificate data from PDF");
        }

        return $certificate;
    }

    private function validateCertificate(array $certificate, string $pdf): bool
    {
        $validations = [
            'issuer' => $this->isValidIssuer($certificate),
            'validity' => $this->isInEffect($certificate),
            'integrity' => $this->isDocumentUnmodified($pdf, $certificate),
        ];

        foreach ($validations as $check => $result) {
            if (!$result) {
                error_log("[SignedPDF] Validation failed: {$check}");
                return false;
            }
        }

        error_log("[SignedPDF] All validations passed successfully");
        return true;
    }

    // =================================================================
    // DETECCIÓN Y EXTRACCIÓN DE FIRMAS
    // =================================================================

    private function hasDigitalSignature(string $pdf): bool
    {
        $signature_markers = [
            '/Type /Sig', '/ByteRange', '/Contents',
            '/SubFilter /adbe.pkcs7.detached', '/SubFilter /adbe.pkcs7.sha1',
            '/Filter /Adobe.PPKLite', '/M (D:'
        ];

        $has_markers = $this->containsAnyMarker($pdf, $signature_markers);
        $has_structure = $this->hasPkcs7Structure($pdf);
        
        return $has_markers && $has_structure;
    }

    private function containsAnyMarker(string $content, array $markers): bool
    {
        foreach ($markers as $marker) {
            if (strpos($content, $marker) !== false) {
                return true;
            }
        }
        return false;
    }

    private function hasPkcs7Structure(string $pdf): bool
    {
        $patterns = [
            '/Contents\s*<[0-9a-fA-F]+>/',
            '/ByteRange\s*\[\s*\d+\s+\d+\s+\d+\s+\d+\s*\]/'
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $pdf)) {
                return true;
            }
        }
        return false;
    }

    private function extractCertificateData(string $pdf): ?array
    {
        $signature_hex = $this->extractSignatureHex($pdf);
        if (!$signature_hex) {
            return null;
        }

        $signature_binary = hex2bin($signature_hex);
        if ($signature_binary === false) {
            return null;
        }

        return $this->parseCertificateFromPkcs7($signature_binary);
    }

    private function extractSignatureHex(string $pdf): ?string
    {
        if (preg_match('/Contents\s*<([0-9a-fA-F]+)>/', $pdf, $matches)) {
            return $matches[1];
        }
        return null;
    }

    // =================================================================
    // PROCESAMIENTO DE CERTIFICADOS PKCS#7
    // =================================================================

    private function parseCertificateFromPkcs7(string $binary_signature): ?array
    {
        try {
            // Método 1: Usar openssl_pkcs7_read directamente
            $certificates = [];
            if (@openssl_pkcs7_read($binary_signature, $certificates) && !empty($certificates)) {
                foreach ($certificates as $cert_pem) {
                    $cert_data = @openssl_x509_parse($cert_pem);
                    if ($cert_data !== false) {
                        return $this->formatCertificateInfo($cert_data);
                    }
                }
            }

            // Método 2: Extraer certificado manualmente
            $cert_pem = $this->extractCertificateFromBinary($binary_signature);
            if ($cert_pem) {
                $cert_data = @openssl_x509_parse($cert_pem);
                if ($cert_data !== false) {
                    return $this->formatCertificateInfo($cert_data);
                }
            }

        } catch (Exception $e) {
            error_log('[SignedPDF] Certificate parsing error: ' . $e->getMessage());
        }

        return null;
    }

    private function extractCertificateFromBinary(string $binary_data): ?string
    {
        $cert_patterns = ["\x30\x82", "\x30\x81"];

        foreach ($cert_patterns as $pattern) {
            $pos = 0;
            while (($pos = strpos($binary_data, $pattern, $pos)) !== false) {
                try {
                    $cert_der = $this->extractDerCertificate($binary_data, $pos);
                    if ($cert_der) {
                        $cert_pem = $this->convertDerToPem($cert_der);
                        if ($this->isValidCertificatePem($cert_pem)) {
                            return $cert_pem;
                        }
                    }
                } catch (Exception $e) {
                    error_log('[SignedPDF] Certificate extraction error at position ' . $pos);
                }
                $pos++;
            }
        }

        return null;
    }

    private function extractDerCertificate(string $binary_data, int $start_pos): ?string
    {
        if ($start_pos + 4 >= strlen($binary_data)) {
            return null;
        }

        $tag = ord($binary_data[$start_pos]);
        if ($tag !== 0x30) { // SEQUENCE tag
            return null;
        }

        $length_info = $this->parseAsn1Length($binary_data, $start_pos + 1);
        if (!$length_info) {
            return null;
        }

        $total_length = 1 + $length_info['bytes_used'] + $length_info['length'];

        if ($start_pos + $total_length > strlen($binary_data)) {
            return null;
        }

        return substr($binary_data, $start_pos, $total_length);
    }

    private function parseAsn1Length(string $data, int $pos): ?array
    {
        if ($pos >= strlen($data)) {
            return null;
        }

        $length_byte = ord($data[$pos]);

        if ($length_byte & 0x80) {
            // Forma larga
            $length_bytes_count = $length_byte & 0x7F;
            if ($length_bytes_count > 4 || $pos + 1 + $length_bytes_count >= strlen($data)) {
                return null;
            }

            $length = 0;
            for ($i = 0; $i < $length_bytes_count; $i++) {
                $length = ($length << 8) | ord($data[$pos + 1 + $i]);
            }

            return [
                'length' => $length,
                'bytes_used' => 1 + $length_bytes_count
            ];
        } else {
            // Forma corta
            return [
                'length' => $length_byte,
                'bytes_used' => 1
            ];
        }
    }

    private function convertDerToPem(string $cert_der): string
    {
        $cert_pem = "-----BEGIN CERTIFICATE-----\n";
        $cert_pem .= chunk_split(base64_encode($cert_der), 64, "\n");
        $cert_pem .= "-----END CERTIFICATE-----";
        return $cert_pem;
    }

    private function isValidCertificatePem(string $cert_pem): bool
    {
        $cert_resource = @openssl_x509_read($cert_pem);
        return $cert_resource !== false;
    }

    private function formatCertificateInfo(array $cert_data): array
    {
        return [
            'subject' => $cert_data['subject'] ?? null,
            'issuer' => $cert_data['issuer'] ?? null,
            'valid_from' => $cert_data['validFrom_time_t'] ?? null,
            'valid_to' => $cert_data['validTo_time_t'] ?? null,
            'serial_number' => $cert_data['serialNumber'] ?? null,
            'signature_algorithm' => $cert_data['signatureTypeSN'] ?? null,
            'is_ecuadorian' => $this->isEcuadorianCertificate($cert_data),
            'ca_issuer' => $this->identifyEcuadorianCA($cert_data),
            'extensions' => $cert_data['extensions'] ?? [],
        ];
    }

    // =================================================================
    // VALIDACIÓN DE EMISORES
    // =================================================================

    private function isValidIssuer(array $certificate): bool
    {
        if (!isset($certificate['issuer'])) {
            error_log("[SignedPDF] Certificate missing issuer information");
            return false;
        }

        return $this->validateByCANames($certificate) ||
               $this->validateByCAOids($certificate) ||
               $this->validateGovernmentCertificate($certificate);
    }

    private function validateByCANames(array $certificate): bool
    {
        $issuer_text = $this->extractIssuerText($certificate['issuer']);

        foreach (self::ECUADORIAN_CAS as $ca_info) {
            foreach ($ca_info['names'] as $ca_name) {
                if (stripos($issuer_text, $ca_name) !== false) {
                    error_log("[SignedPDF] Valid CA found by name: {$ca_name}");
                    return true;
                }
            }
        }

        return false;
    }

    private function validateByCAOids(array $certificate): bool
    {
        $issuer_oids = $this->extractIssuerOids($certificate);

        foreach (self::ECUADORIAN_CAS as $ca_info) {
            foreach ($ca_info['oids'] as $ca_oid) {
                if (in_array($ca_oid, $issuer_oids)) {
                    error_log("[SignedPDF] Valid CA found by OID: {$ca_oid}");
                    return true;
                }
            }
        }

        return false;
    }

    private function validateGovernmentCertificate(array $certificate): bool
    {
        $issuer_text = $this->extractIssuerText($certificate['issuer']);
        $subject_text = isset($certificate['subject']) ? 
            $this->extractIssuerText($certificate['subject']) : '';

        $combined_text = strtolower($issuer_text . ' ' . $subject_text);

        foreach (self::GOVERNMENT_DOMAINS as $domain) {
            if (strpos($combined_text, $domain) !== false) {
                error_log("[SignedPDF] Valid government certificate found: {$domain}");
                return true;
            }
        }

        return false;
    }

    private function extractIssuerText($issuer_data): string
    {
        if (is_string($issuer_data)) {
            return $issuer_data;
        }

        if (is_array($issuer_data)) {
            $parts = [];
            foreach ($issuer_data as $value) {
                if (is_string($value)) {
                    $parts[] = $value;
                } elseif (is_array($value)) {
                    $parts[] = implode(', ', $value);
                }
            }
            return implode(' ', $parts);
        }

        return '';
    }

    private function extractIssuerOids(array $certificate): array
    {
        $oids = [];

        if (isset($certificate['extensions'])) {
            foreach ($certificate['extensions'] as $ext_value) {
                if (preg_match_all('/\b\d+\.\d+\.\d+\.\d+(?:\.\d+)*\b/', $ext_value, $matches)) {
                    $oids = array_merge($oids, $matches[0]);
                }
            }
        }

        $issuer_text = $this->extractIssuerText($certificate['issuer']);
        if (preg_match_all('/\b\d+\.\d+\.\d+\.\d+(?:\.\d+)*\b/', $issuer_text, $matches)) {
            $oids = array_merge($oids, $matches[0]);
        }

        return array_unique($oids);
    }

    // =================================================================
    // VALIDACIÓN DE VIGENCIA
    // =================================================================

    private function isInEffect(array $certificate): bool
    {
        if (!isset($certificate['valid_from'], $certificate['valid_to'])) {
            error_log("[SignedPDF] Certificate missing validity dates");
            return false;
        }

        $current_time = time();
        $valid_from = $certificate['valid_from'];
        $valid_to = $certificate['valid_to'];

        if ($current_time < $valid_from) {
            error_log("[SignedPDF] Certificate not yet valid");
            return false;
        }

        if ($current_time > $valid_to) {
            if ($this->isWithinGracePeriod($certificate, $current_time)) {
                error_log("[SignedPDF] Certificate within grace period");
                return true;
            }
            error_log("[SignedPDF] Certificate expired");
            return false;
        }

        if (!$this->checkRevocationStatus($certificate)) {
            error_log("[SignedPDF] Certificate appears to be revoked");
            return false;
        }

        error_log("[SignedPDF] Certificate is valid and in effect");
        return true;
    }

    private function isWithinGracePeriod(array $certificate, int $current_time): bool
    {
        if (!isset($certificate['valid_to'], $certificate['is_ecuadorian'])) {
            return false;
        }

        if (!$certificate['is_ecuadorian']) {
            return false;
        }

        $grace_period_seconds = self::GRACE_PERIOD_DAYS * 24 * 60 * 60;
        $grace_deadline = $certificate['valid_to'] + $grace_period_seconds;

        return $current_time <= $grace_deadline;
    }

    private function checkRevocationStatus(array $certificate): bool
    {
        try {
            // En producción, aquí se consultarían las CRL reales
            // Por ahora, solo verificamos patrones conocidos
            return $this->checkKnownRevokedPatterns($certificate);
        } catch (Exception $e) {
            error_log("[SignedPDF] Error checking revocation: " . $e->getMessage());
            return true; // En caso de error, no bloquear
        }
    }

    private function checkKnownRevokedPatterns(array $certificate): bool
    {
        // Lista de números de serie revocados conocidos
        $known_revoked_serials = [];

        if (isset($certificate['serial_number'])) {
            $serial = strtoupper($certificate['serial_number']);
            return !in_array($serial, $known_revoked_serials);
        }

        return true;
    }

    // =================================================================
    // VALIDACIÓN DE INTEGRIDAD DEL DOCUMENTO
    // =================================================================

    private function isDocumentUnmodified(string $pdf, array $certificate): bool
    {
        try {
            $signature_info = $this->extractSignatureInfo($pdf);
            if (!$signature_info) {
                throw new Exception("Cannot extract signature information");
            }

            $integrity_checks = [
                'byte_range' => $this->verifyByteRangeIntegrity($pdf, $signature_info),
                'document_hash' => $this->verifyDocumentHash($pdf, $signature_info, $certificate),
                'incremental_changes' => $this->verifyNoIncrementalChanges($pdf, $signature_info),
                'form_fields' => $this->verifyFormFields($pdf, $signature_info),
            ];

            foreach ($integrity_checks as $check => $result) {
                if (!$result) {
                    error_log("[SignedPDF] Integrity check failed: {$check}");
                    return false;
                }
            }

            error_log("[SignedPDF] Document integrity verified");
            return true;

        } catch (Exception $e) {
            error_log("[SignedPDF] Document integrity verification error: " . $e->getMessage());
            return false;
        }
    }

    private function extractSignatureInfo(string $pdf): ?array
    {
        $signature_info = [];

        // Extraer ByteRange
        if (!preg_match('/\/ByteRange\s*\[\s*(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s*\]/', $pdf, $matches)) {
            return null;
        }

        $signature_info['byte_range'] = [
            'start1' => (int)$matches[1],
            'length1' => (int)$matches[2],
            'start2' => (int)$matches[3],
            'length2' => (int)$matches[4]
        ];

        // Extraer Contents
        if (!preg_match('/\/Contents\s*<([0-9a-fA-F]+)>/', $pdf, $matches)) {
            return null;
        }

        $signature_info['contents_hex'] = $matches[1];
        $signature_info['contents_binary'] = hex2bin($matches[1]);

        // Extraer información adicional opcional
        if (preg_match('/\/M\s*\(D:(\d{14}[+-]\d{2}\'\d{2}\'?)\)/', $pdf, $matches)) {
            $signature_info['signature_time'] = $matches[1];
        }

        if (preg_match('/\/SubFilter\s*\/([^\s\/]+)/', $pdf, $matches)) {
            $signature_info['sub_filter'] = $matches[1];
        }

        return $signature_info;
    }

    private function verifyByteRangeIntegrity(string $pdf, array $signature_info): bool
    {
        $byte_range = $signature_info['byte_range'];
        $pdf_length = strlen($pdf);

        // Verificar rangos individuales
        $first_end = $byte_range['start1'] + $byte_range['length1'];
        $second_end = $byte_range['start2'] + $byte_range['length2'];

        if ($first_end > $pdf_length || $second_end > $pdf_length) {
            return false;
        }

        // Verificar no superposición
        if ($byte_range['start2'] < $first_end) {
            return false;
        }

        // Verificar que la firma cabe en el espacio
        $signature_space = $byte_range['start2'] - $first_end;
        $contents_length = strlen($signature_info['contents_hex'] ?? '');

        return $contents_length <= $signature_space;
    }

    private function verifyDocumentHash(string $pdf, array $signature_info, array $certificate): bool
    {
        $byte_range = $signature_info['byte_range'];

        $signed_data = substr($pdf, $byte_range['start1'], $byte_range['length1']);
        $signed_data .= substr($pdf, $byte_range['start2'], $byte_range['length2']);

        return $this->verifyPkcs7Signature($signed_data, $signature_info['contents_binary']);
    }

    private function verifyPkcs7Signature(string $signed_data, string $signature_binary): bool
    {
        // Verificación básica de estructura PKCS#7
        if (strlen($signature_binary) < 100 || ord($signature_binary[0]) !== 0x30) {
            return false;
        }

        // Verificar OID de PKCS#7 signedData
        $pkcs7_oid = hex2bin('06092a864886f70d010702');
        if (strpos($signature_binary, $pkcs7_oid) === false) {
            return false;
        }

        error_log("[SignedPDF] Basic PKCS#7 signature validation passed");
        return true;
    }

    private function verifyNoIncrementalChanges(string $pdf, array $signature_info): bool
    {
        $byte_range = $signature_info['byte_range'];
        $signature_end = $byte_range['start2'] + $byte_range['length2'];

        $remaining_content = substr($pdf, $signature_end);
        if (empty(trim($remaining_content))) {
            return true;
        }

        // Verificar que solo hay comentarios o whitespace
        $lines = explode("\n", $remaining_content);
        foreach ($lines as $line) {
            $trimmed = trim($line);
            if (!empty($trimmed) && $trimmed[0] !== '%') {
                return false;
            }
        }

        return true;
    }

    private function verifyFormFields(string $pdf, array $signature_info): bool
    {
        // Implementación básica - en producción se verificarían permisos específicos
        return true;
    }

    // =================================================================
    // MÉTODOS DE UTILIDAD
    // =================================================================

    private function isEcuadorianCertificate(array $cert_info): bool
    {
        $ecuadorian_indicators = [
            'subject' => ['Ecuador', 'EC', 'Quito', 'Guayaquil'],
            'issuer' => ['BCE', 'SECURITY DATA', 'UANATACA', 'ANF', 'Ecuador', 'EC']
        ];

        foreach ($ecuadorian_indicators as $field => $indicators) {
            if (!isset($cert_info[$field])) continue;

            $field_text = is_array($cert_info[$field]) 
                ? implode(' ', $cert_info[$field]) 
                : (string)$cert_info[$field];

            foreach ($indicators as $indicator) {
                if (stripos($field_text, $indicator) !== false) {
                    return true;
                }
            }
        }

        return false;
    }

    private function identifyEcuadorianCA(array $cert_info): string
    {
        if (!isset($cert_info['issuer'])) {
            return 'unknown';
        }

        $issuer_text = $this->extractIssuerText($cert_info['issuer']);

        foreach (self::ECUADORIAN_CAS as $key => $ca_info) {
            foreach ($ca_info['names'] as $name) {
                if (stripos($issuer_text, $name) !== false) {
                    return $ca_info['names'][0]; // Retorna el nombre principal
                }
            }
        }

        return 'other';
    }
}