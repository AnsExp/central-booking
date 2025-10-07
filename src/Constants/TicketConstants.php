<?php
namespace CentralTickets\Constants;

final class TicketConstants
{
    public const CANCEL     =   'cancel';
    public const PAYMENT    =   'payment';
    public const PENDING    =   'pending';
    public const PARTIAL    =   'partial';

    /**
     * @return string[]
     */
    public static function all_status(): array
    {
        return [
            self::CANCEL,
            self::PAYMENT,
            self::PARTIAL,
            self::PENDING
        ];
    }

    public static function is_valid_status(string $status): bool
    {
        return in_array(strtolower($status), self::all_status(), true);
    }
}
