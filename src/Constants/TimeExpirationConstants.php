<?php
namespace CentralTickets\Constants;

final class TimeExpirationConstants
{
    public const MINUTES_15 = 900;
    public const MINUTES_30 = 1800;
    public const ONE_HOUR = 3600;
    public const THREE_HOURS = 10800;
    public const SIX_HOURS = 21600;
    public const TWELVE_HOURS = 43200;
    public const ONE_DAY = 86400;
    public const THREE_DAYS = 259200;
    public const SEVEN_DAYS = 604800;
    public const NEVER = -1;

    public static function get_all(){
        return [
            '15_minutes' => self::MINUTES_15,
            '30_minutes' => self::MINUTES_30,
            '1_hour' => self::ONE_HOUR,
            '3_hours' => self::THREE_HOURS,
            '6_hours' => self::SIX_HOURS,
            '12_hours' => self::TWELVE_HOURS,
            '1_day' => self::ONE_DAY,
            '3_days' => self::THREE_DAYS,
            '7_days' => self::SEVEN_DAYS,
            'never' => self::NEVER,
        ];
    }

    public static function valid(int $value)
    {
        return in_array($value, self::get_all(), true);
    }
}
