<?php
namespace CentralBooking\Webhook;

enum WebhookStatus: string
{
    case ACTIVE = 'active';
    case IN_PAUSE = 'in_pause';
    case DISABLED = 'disabled';

    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => 'Activo',
            self::IN_PAUSE => 'En pausa',
            self::DISABLED => 'Deshabilitado',
            default => 'Estado desconocido',
        };
    }
}