<?php
namespace CentralTickets;

use CentralTickets\Constants\TimeExpirationConstants;
use CentralTickets\Constants\ConnectorStatusConstants;
use CentralTickets\Operator;
use CentralTickets\MetaManager;
use DateTime;
use DateTimeZone;

class ConnectorManager
{
    private static ?ConnectorManager $instance = null;

    public static function get_instance(): ConnectorManager
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Crear una nueva clave de conexión
     */
    public function create_key(Operator $operator, int $expiration = TimeExpirationConstants::NEVER): string
    {
        if (!TimeExpirationConstants::valid($expiration)) {
            $expiration = TimeExpirationConstants::NEVER;
        }

        $date = new DateTime('now', new DateTimeZone('GMT'));

        $payload = [
            'status' => ConnectorStatusConstants::ACTIVE,
            'operator' => $operator->user_login,
            'timestamp' => $date->getTimestamp(),
        ];

        if ($expiration !== TimeExpirationConstants::NEVER) {
            $payload['expiration'] = $date->getTimestamp() + $expiration;
        }

        return $this->encrypt(json_encode($payload));
    }

    /**
     * Guardar clave en la base de datos
     */
    public function save_key(string $operator_username, string $key): void
    {
        MetaManager::set_meta(
            'connector',
            1,
            'connector_' . $operator_username,
            $key
        );
    }

    /**
     * Recuperar clave desde la base de datos
     */
    public function get_key(string $operator_username): ?string
    {
        return MetaManager::get_meta(
            'connector',
            1,
            'connector_' . $operator_username
        );
    }

    public function get_username_by_key(string $key): ?string
    {
        $payload = $this->decrypt($key);
        return $payload['operator'] ?? null;
    }

    /**
     * Validar si una clave es válida
     */
    public function validate_key(string $key): bool
    {
        $payload = $this->decrypt($key);

        if (empty($payload)) {
            return false;
        }

        // Verificar que el operador existe
        $operator = git_get_operator_by_username($payload['operator'] ?? '');
        if (!$operator) {
            return false;
        }

        // Verificar que esté activa
        if (($payload['status'] ?? null) !== ConnectorStatusConstants::ACTIVE) {
            return false;
        }

        // Verificar expiración
        if (isset($payload['expiration']) && $payload['expiration'] < time()) {
            return false;
        }

        return true;
    }

    /**
     * Desactivar/borrar una clave (marcarla como usada)
     */
    public function revoke_key(string $operator_username): bool
    {
        $key = $this->get_key($operator_username);

        if (!$key) {
            return false;
        }

        $payload = $this->decrypt($key);

        if (empty($payload)) {
            return false;
        }

        // Cambiar estado a inactivo
        $payload['status'] = ConnectorStatusConstants::INACTIVE;

        // Guardar clave desactivada
        $inactive_key = $this->encrypt(json_encode($payload));
        $this->save_key($operator_username, $inactive_key);

        return true;
    }

    // --- Métodos privados para encriptación ---

    private function encrypt(string $plaintext): string
    {
        $key = hash('sha256', git_get_secret_key(), true);
        $ivlen = openssl_cipher_iv_length('aes-256-cbc');
        $iv = random_bytes($ivlen);
        $cipher = openssl_encrypt($plaintext, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv);
        $mac = hash_hmac('sha256', $iv . $cipher, $key, true);

        return base64_encode($iv . $mac . $cipher);
    }

    private function decrypt(string $token): array
    {
        $raw = base64_decode($token, true);
        if ($raw === false) {
            return [];
        }

        $key = hash('sha256', git_get_secret_key(), true);
        $ivlen = openssl_cipher_iv_length('aes-256-cbc');
        $iv = substr($raw, 0, $ivlen);
        $mac = substr($raw, $ivlen, 32);
        $cipher = substr($raw, $ivlen + 32);
        
        $expected = hash_hmac('sha256', $iv . $cipher, $key, true);
        
        if (!hash_equals($expected, $mac)) {
            return [];
        }

        $plain = openssl_decrypt($cipher, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv);
        if ($plain === false) {
            return [];
        }

        return json_decode($plain, true) ?? [];
    }
}