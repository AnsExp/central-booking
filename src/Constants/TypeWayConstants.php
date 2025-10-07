<?php
namespace CentralTickets\Constants;

final class TypeWayConstants
{
    public const ONE_WAY = 'one_way';
    public const DOUBLE_WAY = 'double_way';
    public const ANY_WAY = 'any_way';

    public static function all_types()
    {
        return [
            self::ONE_WAY,
            self::DOUBLE_WAY,
            self::ANY_WAY
        ];
    }
}
