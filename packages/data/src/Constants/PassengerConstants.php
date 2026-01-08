<?php
namespace CentralBooking\Data\Constants;

enum PassengerConstants: string
{
    case KID = 'kid';
    case RPM = 'rpm';
    case STANDARD = 'standard';

    public function display()
    {
        return match ($this) {
            self::KID => 'Niño',
            self::RPM => 'Movilidad Reducida',
            self::STANDARD => 'Estándar',
            default => $this->value,
        };
    }
}
