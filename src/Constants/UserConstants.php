<?php
namespace CentralTickets\Constants;

final class UserConstants
{
    public const OPERATOR = 'operator';
    public const CUSTOMER = 'customer';
    public const MARKETER = 'marketer';
    public const ADMINISTRATOR = 'administrator';

    public static function all()
    {
        return [
            self::OPERATOR,
            self::CUSTOMER,
            self::MARKETER,
            self::ADMINISTRATOR,
        ];
    }
}
