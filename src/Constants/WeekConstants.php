<?php
namespace CentralTickets\Constants;

final class WeekConstants
{
    public const MONDAY     =   'monday';
    public const TUESDAY    =   'tuesday';
    public const WEDNESDAY  =   'wednesday';
    public const THURSDAY   =   'thursday';
    public const FRIDAY     =   'friday';
    public const SATURDAY   =   'saturday';
    public const SUNDAY     =   'sunday';

    public static function allDays(): array
    {
        return [
            self::MONDAY,
            self::TUESDAY,
            self::WEDNESDAY,
            self::THURSDAY,
            self::FRIDAY,
            self::SATURDAY,
            self::SUNDAY
        ];
    }

    public static function isValidDay(string $day): bool
    {
        return in_array(
            $day,
            self::allDays(),
            true
        );
    }
}
