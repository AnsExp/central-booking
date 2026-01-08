<?php
namespace CentralBooking\Data\Constants;

use CentralBooking\Admin\Setting\SettingsTexts;

enum TransportConstants: string
{
    case NONE = 'none';
    case AERO = 'aero';
    case LAND = 'land';
    case MARINE = 'marine';

    public function label(): string
    {
        $text = SettingsTexts::getTextTransport($this);
        if ($text !== null) {
            return $text;
        }
        return match ($this) {
            self::AERO => 'Aéreo',
            self::LAND => 'Terrestre',
            self::MARINE => 'Marítimo',
            default => $this->value,
        };
    }
}
