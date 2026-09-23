<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Document categories per BRD §10.2 / §13: identity card recto/verso,
 * vehicle registration card (carte grise), provider photo, vehicle photos.
 * Extensible — new types are added as rows, no schema redesign needed.
 */
enum ProviderDocumentType: string
{
    case IdCardFront = 'id_card_front';
    case IdCardBack = 'id_card_back';
    case GrayCard = 'gray_card';
    case ProfilePhoto = 'profile_photo';
    case VehiclePhoto = 'vehicle_photo';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}