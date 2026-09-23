<?php

declare(strict_types=1);

namespace App\Enums;

enum DevicePlatform: string
{
    case Android = 'android';
    case Ios = 'ios';
    case Web = 'web';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}