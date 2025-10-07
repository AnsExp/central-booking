<?php
namespace CentralTickets\Constants;

final class RouteConstants
{
    public const BETWEEN_ZONES   = 'between_zones';
    public const BETWEEN_LOCATIONS = 'between_locations';

    public static function allTypes()
    {
        return [
            self::BETWEEN_ZONES,
            self::BETWEEN_LOCATIONS
        ];
    }

    public static function is_valid(string $type)
    {
        return in_array(strtolower($type), self::allTypes(), true);
    }
}
