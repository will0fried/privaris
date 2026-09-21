<?php

namespace App\Enum;

/**
 * Les formats « signature » sériels du carnet Privaris.
 * Optionnel sur une entrée : une entrée peut n'appartenir à aucune série.
 */
enum EntrySerie: string
{
    case REJEU = 'rejeu';
    case VENDU_IA = 'vendu_ia';

    public function label(): string
    {
        return match ($this) {
            self::REJEU => 'Rejeu',
            self::VENDU_IA => 'VenduIA',
        };
    }

    /**
     * Classe CSS de l'étiquette de série (voir app.css).
     */
    public function cssClass(): string
    {
        return match ($this) {
            self::REJEU => 'rejeu',
            self::VENDU_IA => 'venduia',
        };
    }

    /**
     * Ce que promet la série, en une phrase (aide admin / infobulle).
     */
    public function description(): string
    {
        return match ($this) {
            self::REJEU => 'Je rejoue un incident public et je regarde si une défense automatisée l\'aurait vu.',
            self::VENDU_IA => 'Je teste un outil vendu « IA » et je montre ce qu\'il rate.',
        };
    }
}
