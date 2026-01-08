<?php
namespace CentralBooking\Data\Constants;

use CentralBooking\Admin\Setting\SettingsTexts;

enum TypeWayConstants: string
{
    case ONE_WAY = 'one_way';
    case DOUBLE_WAY = 'double_way';
    case ANY_WAY = 'any_way';

    public function label(): string
    {
        $text = SettingsTexts::getTextWays($this);
        if ($text !== null) {
            return $text;
        }
        return match ($this) {
            self::ONE_WAY => 'Ida',
            self::DOUBLE_WAY => 'Ida y Vuelta',
            self::ANY_WAY => 'Ambos',
            default => $this->value,
        };
    }
}
