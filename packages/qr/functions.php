<?php

use CentralBooking\QR\CodeQr;
use CentralBooking\QR\ColorQr;
use CentralBooking\QR\DataQr;
use CentralBooking\QR\ErrorCorrectionCode;

function git_qr_create(DataQr $data, array $params = [])
{
    $size = 350;
    $margin = 10;
    $color = '#000000';
    $bgColor = '#ffffff';
    if (
        isset($params['size']) &&
        is_int($params['size']) &&
        $params['size'] > 50
    ) {
        $size = intval($params['size']);
    }
    if (
        isset($params['margin']) &&
        is_int($params['margin']) &&
        $params['margin'] > 0 &&
        $params['margin'] <= 50
    ) {
        $margin = intval($params['margin']);
    }
    if (
        isset($params['color']) &&
        is_string($params['color']) &&
        preg_match('/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/', $params['color'])
    ) {
        $color = $params['color'];
    }
    if (
        isset($params['bg_color']) &&
        is_string($params['bg_color']) &&
        preg_match('/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/', $params['bgColor'])
    ) {
        $bgColor = $params['bg_color'];
    }
    $code_qr = CodeQr::create(
        $data,
        $params['error_correction_code'] ?? ErrorCorrectionCode::LOW,
        $size,
        $margin,
        ColorQr::fromHex($color),
        ColorQr::fromHex($bgColor)
    );
    return $code_qr;
}