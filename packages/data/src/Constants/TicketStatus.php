<?php
namespace CentralBooking\Data\Constants;

use CentralBooking\Admin\Setting\SettingsTexts;

enum TicketStatus: string
{
    case CANCEL = 'cancel';
    case PAYMENT = 'payment';
    case PENDING = 'pending';
    case PARTIAL = 'partial';
    case PERORDER = 'perorder';

    public function label()
    {
        $text = SettingsTexts::getTextStatus($this);
        if ($text !== null) {
            return $text;
        }
        return match ($this) {
            self::CANCEL => 'Anulado',
            self::PAYMENT => 'Pagado',
            self::PARTIAL => 'Parcial',
            self::PENDING => 'Pendiente',
            self::PERORDER => 'Preorden',
            default => $this->value
        };
    }
}
