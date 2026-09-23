<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Account statuses per BRD §10.
 * Clients: actif / suspendu / banni / supprimé.
 * Providers additionally start as en_attente (pending manual admin verification).
 */
enum AccountStatus: string
{
    case Pending = 'en_attente';
    case Active = 'actif';
    case Suspended = 'suspendu';
    case Banned = 'banni';
    case Deleted = 'supprime';

    public static function clientValues(): array
    {
        return [self::Active, self::Suspended, self::Banned, self::Deleted];
    }

    public static function providerValues(): array
    {
        return [self::Pending, self::Active, self::Suspended, self::Banned];
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}