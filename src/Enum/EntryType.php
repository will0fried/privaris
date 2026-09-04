<?php

namespace App\Enum;

/**
 * Les quatre types d'entrées du carnet Privaris.
 */
enum EntryType: string
{
    case LAB = 'lab';
    case WRITEUP = 'writeup';
    case DECRYPTAGE = 'decryptage';
    case COULISSES = 'coulisses';

    public function label(): string
    {
        return match ($this) {
            self::LAB => 'Lab',
            self::WRITEUP => 'Writeup',
            self::DECRYPTAGE => 'Décryptage',
            self::COULISSES => 'Coulisses',
        };
    }

    /**
     * Classe CSS utilisée pour colorer l'étiquette du type (voir app.css).
     */
    public function cssClass(): string
    {
        return match ($this) {
            self::LAB => 'lab',
            self::WRITEUP => 'wri',
            self::DECRYPTAGE => 'dec',
            self::COULISSES => 'cou',
        };
    }

    /**
     * @return array<string, string> label => value, pour les formulaires
     */
    public static function choices(): array
    {
        $choices = [];
        foreach (self::cases() as $case) {
            $choices[$case->label()] = $case->value;
        }

        return $choices;
    }
}
