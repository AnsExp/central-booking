<?php
namespace CentralBooking\Webhook;

enum WebhookTopic: string
{
    case NONE = 'none';
    case COUPON_USED = 'coupon_used';
    case TICKET_CREATE = 'ticket_create';
    case TICKET_UPDATE = 'ticket_update';
    case INVOICE_UPLOAD = 'invoice_upload';
    case PASSENGER_SERVED = 'passenger_served';
    case PASSENGER_APPROVED = 'passenger_approved';
    case PASSENGER_TRANSFERRED = 'passenger_transferred';

    public function label()
    {
        return match ($this) {
            self::COUPON_USED => 'Cupón utilizado',
            self::TICKET_CREATE => 'Ticket creado',
            self::INVOICE_UPLOAD => 'Factura subida',
            self::TICKET_UPDATE => 'Ticket actualizado',
            self::PASSENGER_SERVED => 'Pasajero atendido',
            self::PASSENGER_APPROVED => 'Pasajero aprobado',
            self::PASSENGER_TRANSFERRED => 'Pasajero trasladado',
            default => 'Estado desconocido',
        };
    }
}