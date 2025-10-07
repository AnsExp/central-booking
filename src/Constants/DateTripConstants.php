<?php
namespace CentralTickets\Constants;

final class DateTripConstants
{
    public const NONE = 'none';
    public const TODAY = 'today';
    public const CUSTOME = 'custome';

    public static function get_all()
    {
        return [
            self::NONE,
            self::TODAY,
            self::CUSTOME,
        ];
    }

    public static function is_valid(string $value): bool
    {
        return in_array($value, self::get_all(), true);
    }
}