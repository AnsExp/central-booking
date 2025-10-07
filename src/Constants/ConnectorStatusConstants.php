<?php
namespace CentralTickets\Constants;

final class ConnectorStatusConstants
{
    public const ACTIVE = 'active';
    public const INACTIVE = 'inactive';
    public const EXPIRED = 'expired';

    public static function get_all()
    {
        return [
            self::ACTIVE,
            self::INACTIVE,
            self::EXPIRED,
        ];
    }

    public static function is_valid(string $value): bool
    {
        return in_array($value, self::get_all(), true);
    }
}