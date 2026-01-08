<?php

use CentralBooking\Data\Constants\UserConstants;
use CentralBooking\Data\Date;

function git_get_secret_key()
{
    return git_get_setting('secret_key', 'default_secret_key');
}

function git_set_secret_key(string $key)
{
    return git_set_setting('secret_key', $key);
}

function git_set_days_without_sale(int $days_without_sale)
{
    git_set_setting('days_without_sale', $days_without_sale);
    return true;
}

function git_get_days_without_sale()
{
    return git_get_setting('days_without_sale', 0);
}

function git_date_trip_min()
{
    $offset = git_get_days_without_sale();
    $min_date = new Date;
    if ($offset > 0) {
        $min_date->addDays($offset);
    }
    return $min_date;
}

function git_date_trip_valid(Date $date_trip)
{
    $min_date = git_date_trip_min();
    return $min_date->format('Y-m-d') <= $date_trip->format('Y-m-d');
}

function git_date_create(string $Ymd = 'today')
{
    if ($Ymd === 'today') {
        return Date::today();
    }
    return new Date($Ymd);
}

function git_sanitize_html_content($content)
{
    $allowed_html = wp_kses_allowed_html('post');

    $allowed_html['img'] = [
        'src' => true,
        'alt' => true,
        'class' => true,
        'id' => true,
        'width' => true,
        'height' => true,
        'style' => true,
    ];

    $allowed_html['div'] = [
        'class' => true,
        'id' => true,
        'style' => true,
    ];

    return wp_kses($content, $allowed_html);
}

/**
 * Crear QR code para tickets
 */
function git_create_code_qr(mixed $data, int $size = 350)
{
    $data_serialized = git_serialize($data);

    if ($data_serialized === false) {
        return false;
    }

    return "https://api.qrserver.com/v1/create-qr-code/?size={$size}x{$size}&data={$data_serialized}";
}

function git_get_ticket_viewer_url($data, int $size = 350)
{
    $ticket_viewer_data = git_get_setting('ticket_viewer');

    if ($ticket_viewer_data === null) {
        return '#';
    }

    $permalink = get_permalink($ticket_viewer_data['page_viewer']);

    if ($permalink === false) {
        return null;
    }

    $url = add_query_arg('data', git_serialize($data), $permalink);

    return git_create_code_qr(urlencode($url), $size);
}

function git_get_ticket_viewer_qr_url($data)
{
    $ticket_viewer_data = git_get_setting('ticket_viewer');

    if ($ticket_viewer_data === null) {
        return '#';
    }

    $permalink = get_permalink($ticket_viewer_data['page_viewer']);

    if ($permalink === false) {
        return null;
    }

    $url = add_query_arg('data', git_serialize($data), $permalink);

    return $url;
}

function git_get_url_logo_by_coupon(WP_Post $coupon)
{
    $url = get_post_meta($coupon->ID, 'logo_sale', true);
    if ($url === '') {
        return CENTRAL_BOOKING_URL . 'assets/img/logo-placeholder.png';
    }
    return $url;
}

function git_currency_format($amount, bool $is_cent = true)
{
    if ($is_cent) {
        $amount /= 100;
    }
    return number_format($amount, 2, ',', '.') . '$';
}

function git_time_format(string $time)
{
    return date_format(date_create($time), 'H:i a');
}

function git_duration_format(string $time)
{
    list($hours, $minutes) = explode(":", $time);
    return sprintf("%02dh%02d", $hours, $minutes);
}

function git_date_format(string $date, bool $short = false)
{
    $months = [
        1 => 'enero',
        2 => 'febrero',
        3 => 'marzo',
        4 => 'abril',
        5 => 'mayo',
        6 => 'junio',
        7 => 'julio',
        8 => 'agosto',
        9 => 'septiembre',
        10 => 'octubre',
        11 => 'noviembre',
        12 => 'diciembre'
    ];

    $months_short = [
        1 => 'ene',
        2 => 'feb',
        3 => 'mar',
        4 => 'abr',
        5 => 'may',
        6 => 'jun',
        7 => 'jul',
        8 => 'ago',
        9 => 'sep',
        10 => 'oct',
        11 => 'nov',
        12 => 'dic'
    ];

    $date_obj = date_create($date);
    if ($date_obj === false) {
        return $date;
    }

    $day = date_format($date_obj, 'j');
    $month_num = (int) date_format($date_obj, 'n');
    $year = date_format($date_obj, 'Y');

    if ($short) {
        return "{$day} {$months_short[$month_num]}, {$year}";
    } else {
        return "{$day} de {$months[$month_num]}, {$year}";
    }
}

function git_datetime_format(string $datetime)
{
    $months = [
        1 => 'enero',
        2 => 'febrero',
        3 => 'marzo',
        4 => 'abril',
        5 => 'mayo',
        6 => 'junio',
        7 => 'julio',
        8 => 'agosto',
        9 => 'septiembre',
        10 => 'octubre',
        11 => 'noviembre',
        12 => 'diciembre'
    ];

    $datetime_obj = date_create($datetime);
    if ($datetime_obj === false) {
        return $datetime;
    }

    $day = date_format($datetime_obj, 'j');
    $month_num = (int) date_format($datetime_obj, 'n');
    $year = date_format($datetime_obj, 'Y');
    $time = date_format($datetime_obj, 'G:i');
    $ampm = date_format($datetime_obj, 'A') === 'AM' ? 'am' : 'pm';

    $hour = (int) date_format($datetime_obj, 'G');
    $minute = date_format($datetime_obj, 'i');

    if ($hour == 0) {
        $hour_12 = 12;
    } elseif ($hour > 12) {
        $hour_12 = $hour - 12;
    } else {
        $hour_12 = $hour;
    }

    $time_formatted = sprintf("%d:%s %s", $hour_12, $minute, $ampm);

    return "{$day} de {$months[$month_num]}, {$year} {$time_formatted}";
}

function git_user_logged_in()
{
    return is_user_logged_in();
}

/**
 * @param UserConstants|string $role Roles pertinentes a la aplicación: 'operator', 'administrator', 'customer'.
 * @return bool
 */
function git_current_user_has_role(UserConstants|string $role)
{
    if (!is_user_logged_in()) {
        return false;
    }

    $user = wp_get_current_user();
    return in_array($role instanceof UserConstants ? $role->value : $role, $user->roles, true);
}

function git_role_user()
{
    if (!is_user_logged_in()) {
        return null;
    }

    $user = wp_get_current_user();
    return $user->roles[0] ?? null;
}

function sanitize_operator_file_size($input): int|false
{
    $input = trim($input);

    if (empty($input)) {
        return false;
    }

    $size = filter_var($input, FILTER_VALIDATE_INT, [
        'options' => [
            'min_range' => 1,
            'max_range' => 1024
        ]
    ]);

    if ($size === false) {
        $float_size = filter_var($input, FILTER_VALIDATE_FLOAT, [
            'options' => [
                'min_range' => 0.1,
                'max_range' => 1024.0
            ]
        ]);

        if ($float_size !== false) {
            return (int) ceil($float_size);
        }

        return false;
    }

    return $size;
}

function sanitize_operator_file_extensions($input): array|false
{
    $input = trim($input);

    if (empty($input)) {
        return [];
    }

    $extensions = array_map('trim', explode(',', $input));
    $sanitized_extensions = [];

    foreach ($extensions as $extension) {
        $extension = trim($extension);

        if (empty($extension)) {
            continue;
        }

        $extension = preg_replace('/[^a-zA-Z0-9.]/', '', $extension);

        if (!str_starts_with($extension, '.')) {
            $extension = '.' . $extension;
        }

        if (strlen($extension) < 3) {
            continue;
        }

        $extension = strtolower($extension);

        $allowed_extensions = [
            '.jpg',
            '.jpeg',
            '.png',
            '.gif',
            '.webp',
            '.svg',
            '.bmp',
            '.ico',
            '.tiff',
            '.pdf',
            '.doc',
            '.docx',
            '.xls',
            '.xlsx',
            '.ppt',
            '.pptx',
            '.txt',
            '.rtf',
            '.zip',
            '.rar',
            '.7z',
            '.tar',
            '.gz',
            '.mp4',
            '.avi',
            '.mov',
            '.wmv',
            '.flv',
            '.webm',
            '.mp3',
            '.wav',
            '.ogg',
            '.m4a',
            '.flac',
            '.css',
            '.js',
            '.html',
            '.xml',
            '.json'
        ];

        if (in_array($extension, $allowed_extensions)) {
            $sanitized_extensions[] = $extension;
        }
    }

    $sanitized_extensions = array_values(array_unique($sanitized_extensions));

    return $sanitized_extensions;
}
