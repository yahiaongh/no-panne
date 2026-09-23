<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Quick review tags per BRD §6.7: Ponctuel, Professionnel, Prix correct,
 * Problème résolu, À éviter.
 */
enum ReviewTag: string
{
    case Punctual = 'ponctuel';
    case Professional = 'professionnel';
    case FairPrice = 'prix_correct';
    case ProblemSolved = 'probleme_resolu';
    case ToAvoid = 'a_eviter';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}