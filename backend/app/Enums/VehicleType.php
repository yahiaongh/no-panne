<?php

declare(strict_types=1);

namespace App\Enums;

enum VehicleType: string
{
    case Moto = 'moto';
    case Car = 'voiture';
    case Van = 'camionnette';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}