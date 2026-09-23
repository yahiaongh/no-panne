<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Request lifecycle per BRD §10.4.
 */
enum RequestStatus: string
{
    case Searching = 'en_recherche';
    case Accepted = 'acceptee';
    case EnRoute = 'en_route';
    case OnSite = 'sur_place';
    case Completed = 'terminee';

    case CancelledByClient = 'annulee_client';
    case CancelledByProvider = 'annulee_prestataire';
    case NoProvider = 'aucun_prestataire';
    case Expired = 'expiree';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Statuses considered "terminal".
     */
    public function isTerminal(): bool
    {
        return in_array($this, [
            self::Completed,
            self::CancelledByClient,
            self::CancelledByProvider,
            self::NoProvider,
            self::Expired,
        ], true);
    }
}