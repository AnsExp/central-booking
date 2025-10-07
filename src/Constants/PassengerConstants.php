<?php
namespace CentralTickets\Constants;

final class PassengerConstants
{
    public const KID        = 'kid';
    public const RPM        = 'rpm';
    public const STANDARD   = 'standard';

    public static function allTypes(): array
    {
        return [
            self::KID,
            self::RPM,
            self::STANDARD,
        ];
    }
}
