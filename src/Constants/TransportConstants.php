<?php
namespace CentralTickets\Constants;

final class TransportConstants
{
    public const AERO   = 'aero';
    public const LAND   = 'land';
    public const MARINE = 'marine';

    public static function all()
    {
        return [
            self::AERO,
            self::LAND,
            self::MARINE
        ];
    }

    public static function is_valid(string $type)
    {
        return in_array($type, self::all(), true);
    }
}
