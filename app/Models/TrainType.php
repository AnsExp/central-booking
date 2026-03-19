<?php

namespace App\Models;

enum TrainType: string
{
    case PASSENGERS = 'passengers';
    case CARGO = 'cargo';
    case MIXED = 'mixed';

    public static function label(TrainType|string $type): string
    {
        $type = $type instanceof TrainType ? $type : self::from($type);
        return match ($type) {
            self::PASSENGERS => 'Pasajeros',
            self::CARGO => 'Carga',
            self::MIXED => 'Mixto',
            default => 'Desconocido',
        };
    }
}
