<?php
namespace CentralBooking\Data\Constants;

enum UserConstants: string
{
    case CUSTOMER = 'customer';
    case OPERATOR = 'operator';
    case MARKETER = 'marketer';
    case ADMINISTRATOR = 'administrator';

    public function display()
    {
        return match ($this) {
            self::CUSTOMER => 'Cliente',
            self::OPERATOR => 'Operador',
            self::MARKETER => 'Comercializador',
            self::ADMINISTRATOR => 'Administrador',
            default => $this->value,
        };
    }
}
