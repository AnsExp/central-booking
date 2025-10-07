<?php
namespace CentralTickets\Constants;

final class WebhookStatusConstants
{
    public const ACTIVE = 'active';
    public const IN_PAUSE = 'in_pause';
    public const DISABLED = 'disabled';

    public static function get_display_name(string $status)
    {
        return match ($status) {
            self::ACTIVE => 'Activo',
            self::IN_PAUSE => 'En pausa',
            self::DISABLED => 'Deshabilitado',
            default => 'Estado desconocido',
        };
    }

    public static function is_valid(string $status)
    {
        return in_array($status, self::get_all(), true);
    }

    public static function get_all()
    {
        return [
            self::ACTIVE,
            self::IN_PAUSE,
            self::DISABLED,
        ];
    }
}