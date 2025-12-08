<?php

use CentralTickets\Components\Component;
use CentralTickets\Configurations;
use CentralTickets\Constants\TicketConstants;
use CentralTickets\Constants\TransportConstants;
use CentralTickets\Constants\TypeWayConstants;
use CentralTickets\MetaManager;
use CentralTickets\Persistence\QueryPersistence;

function git_get_secret_key()
{
    return git_get_setting('preorder_secret_key', 'default_secret_key');
}

function git_set_secret_key(string $key)
{
    return git_set_setting('preorder_secret_key', $key);
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

function git_get_setting(string $key, mixed $default = null)
{
    return Configurations::get($key, $default);
}

function git_get_map_setting(string $key, mixed $default = null)
{
    return Configurations::get_map($key, $default);
}

function git_get_url_logo_by_coupon(WP_Post $coupon)
{
    $url = get_post_meta($coupon->ID, 'logo_sale', true);
    if ($url === '') {
        return CENTRAL_BOOKING_URL . 'assets/img/logo-placeholder.png';
    }
    return $url;
}

function git_get_text_by_type(string $type)
{
    $types = git_get_setting('texts_type', [
        TransportConstants::LAND => 'Terrestre',
        TransportConstants::AERO => 'Aéreo',
        TransportConstants::MARINE => 'Marítimo',
    ]);
    return $types[$type] ?? $type;
}

function git_get_text_by_status(string $status)
{
    $statuses = git_get_setting('texts_status', [
        TicketConstants::PENDING => 'Pendiente',
        TicketConstants::PAYMENT => 'Pagado',
        TicketConstants::PARTIAL => 'Parcial',
        TicketConstants::CANCEL => 'Anulado',
    ]);
    return $statuses[$status] ?? $status;
}

function git_get_text_by_way(string $way)
{
    $ways = git_get_setting('texts_ways', [
        TypeWayConstants::ONE_WAY => 'Ida',
        TypeWayConstants::DOUBLE_WAY => 'Ida y vuelta',
        TypeWayConstants::ANY_WAY => 'Ambos',
    ]);
    return $ways[$way] ?? $way;
}

function git_set_setting(string $key, mixed $value)
{
    return Configurations::set($key, $value);
}

/**
 * Transforma una cadena a cualquier tipo de dato.
 * @param string $value Cadena a parsear.
 * @return mixed Valor parseado, puede ser bool, int, float, null, string o array.
 */
function git_unserialize(string $value): mixed
{
    $decoded = json_decode($value, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        return $decoded;
    }

    $unserialized = @unserialize($value);
    if ($unserialized !== false) {
        return $unserialized;
    }

    if ($value === 'true')
        return true;
    if ($value === 'false')
        return false;
    if ($value === 'null')
        return null;
    if (is_numeric($value)) {
        return strpos($value, '.') !== false ? (float) $value : (int) $value;
    }

    return $value;
}

/**
 * Serializa un valor para almacenarlo en la base de datos.
 * Convierte tipos complejos a JSON, y maneja strings, booleans y nulls adecuadamente.
 * @param mixed $value Valor a serializar. Puede ser un string, boolean, null, int, float o array.
 * @return bool|string Retorna el valor serializado como string. En caso de un error, retorna false.
 */
function git_serialize(mixed $value): string
{
    if (is_string($value)) {
        return $value;
    }

    if (is_bool($value)) {
        return $value ? 'true' : 'false';
    }

    if (is_null($value)) {
        return 'null';
    }

    if (is_scalar($value)) {
        return (string) $value;
    }

    return json_encode($value, JSON_UNESCAPED_UNICODE);
}

function git_currency_format($amount, bool $is_cent)
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

/**
 * Converts a string to a Component instance.
 *
 * @param string $string The string to convert.
 * @return Component The Component instance.
 */
function git_string_to_component($string): Component
{
    return new class ($string) implements Component {
        public function __construct(private $string)
        {
        }
        public function compact()
        {
            return $this->string;
        }
    };
}

function git_user_logged_in()
{
    return is_user_logged_in();
}

/**
 * @param string $role Roles pertinentes a la aplicación: 'operator', 'administrator', 'customer'.
 * @return bool
 */
function git_current_user_has_role(string $role)
{
    if (!is_user_logged_in()) {
        return false;
    }

    $user = wp_get_current_user();
    return in_array($role, $user->roles, true);
}

function git_role_user()
{
    if (!is_user_logged_in()) {
        return null;
    }

    $user = wp_get_current_user();
    return $user->roles[0] ?? null;
}

function git_get_meta(string $type, int $id, string $key)
{
    return MetaManager::get_meta($type, $id, $key);
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

function git_get_query_persistence()
{
    return QueryPersistence::get_instance();
}

function git_get_passenger_by_id(int $id)
{
    $access_data = git_get_query_persistence()->get_passenger_repository();
    $passenger = $access_data->find($id);
    return $passenger ?? false;
}

function git_get_operator_by_id(int $id)
{
    $access_data = git_get_query_persistence()->get_operator_repository();
    $operator = $access_data->find($id);
    return $operator ?? false;
}

function git_get_operator_by_username(string $username)
{
    $access_data = git_get_query_persistence()->get_operator_repository();
    $operator = $access_data->find_first(['username' => $username]);
    return $operator ?? false;
}

function git_get_passengers(array $args = [])
{
    $access_data = git_get_query_persistence()->get_passenger_repository();
    $passenger = $access_data->find_by($args);
    return $passenger;
}

function git_get_route_by_id(int $id)
{
    $route = git_get_query_persistence()->get_route_repository()->find($id);
    return $route ?? false;
}

function git_get_service_by_id(int $id)
{
    $service = git_get_query_persistence()->get_service_repository()->find($id);
    return $service ?? false;
}

function git_get_location_by_id(int $id)
{
    $location = git_get_query_persistence()->get_location_repository()->find($id);
    return $location ?? false;
}

function git_get_locations(array $args = [])
{
    $locations = git_get_query_persistence()->get_location_repository()->find_by($args);
    return $locations;
}

function git_get_transports(array $args = [])
{
    $transports = git_get_query_persistence()->get_transport_repository()->find_by($args);
    return $transports;
}

function git_get_services(array $args = [])
{
    $services = git_get_query_persistence()->get_service_repository()->find_by($args);
    return $services;
}

function git_get_transport_by_id(int $id)
{
    $transport = git_get_query_persistence()->get_transport_repository()->find($id);
    return $transport ?? false;
}

function git_get_ticket_by_id(int $id)
{
    $ticket = git_get_query_persistence()->get_ticket_repository()->find($id);
    return $ticket ?? false;
}

function git_get_routes(array $args = [])
{
    $route = git_get_query_persistence()->get_route_repository()->find_by($args);
    return $route;
}

function git_get_zone_by_id(int $id)
{
    $zone = git_get_query_persistence()->get_zone_repository()->find($id);
    return $zone ?? false;
}

function git_get_zones(array $args = [])
{
    $zones = git_get_query_persistence()->get_zone_repository()->find_by($args);
    return $zones;
}

function git_get_operator_by_coupon(WP_Post $coupon)
{
    $operator_id = get_post_meta($coupon->ID, 'coupon_assigned_operator', true);
    if ($operator_id) {
        return git_get_operator_by_id((int) $operator_id);
    }
    return null;
}

function git_get_logo_by_coupon(WP_Post $coupon)
{
    $url = get_post_meta($coupon->ID, 'logo_sale', true);
    if ($url === '') {
        return $url;
    }
    return '';
}

function git_get_all_coupons()
{
    return git_get_query_persistence()->get_coupon_repository()->find_all();
}

function git_get_coupon_by_id(int $id)
{
    return git_get_query_persistence()->get_coupon_repository()->find($id);
}