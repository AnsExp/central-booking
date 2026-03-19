<?php

namespace App\Models;

enum TrainStatus: string
{
    case ACTIVE = 'active';
    case UNDER_MAINTENANCE = 'under_maintenance';
    case DECOMMISSIONED = 'decommissioned';
    case OUT_OF_SERVICE = 'out_of_service';

    public static function label(TrainStatus|string $status): string
    {
        $status = $status instanceof TrainStatus ? $status : self::from($status);
        return match ($status) {
            self::ACTIVE => 'Activo',
            self::UNDER_MAINTENANCE => 'En mantenimiento',
            self::DECOMMISSIONED => 'Desmantelado',
            self::OUT_OF_SERVICE => 'Fuera de servicio',
            default => 'Desconocido',
        };
    }
}
