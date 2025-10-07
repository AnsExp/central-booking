<?php
namespace CentralTickets\Constants;

final class WebhookTopicConstants
{
    public const COUPON_USED = 'coupon_used';
    public const TICKET_CREATE = 'ticket_create';
    public const TICKET_UPDATE = 'ticket_update';
    public const PASSENGER_SERVED = 'passenger_served';
    public const PASSENGER_APPROVED = 'passenger_approved';
    public const PASSENGER_TRANSFERRED = 'passenger_transferred';

    public static function get_display_name(string $status)
    {
        return match ($status) {
            self::COUPON_USED => 'Cupón utilizado',
            self::TICKET_CREATE => 'Ticket creado',
            self::TICKET_UPDATE => 'Ticket actualizado',
            self::PASSENGER_SERVED => 'Pasajero atendido',
            self::PASSENGER_APPROVED => 'Pasajero aprobado',
            self::PASSENGER_TRANSFERRED => 'Pasajero trasladado',
            default => 'Estado desconocido',
        };
    }

    public static function get_all()
    {
        return [
            self::COUPON_USED,
            self::TICKET_CREATE,
            self::TICKET_UPDATE,
            self::PASSENGER_SERVED,
            self::PASSENGER_APPROVED,
            self::PASSENGER_TRANSFERRED,
        ];
    }

    public static function is_valid(string $status)
    {
        return in_array($status, self::get_all(), true);
    }
}