<?php

declare(strict_types=1);

namespace App\Enums;

enum ServiceType: string
{
    case Emergency = 'urgence';
    case Planned = 'planifie';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}