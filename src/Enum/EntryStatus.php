<?php

namespace App\Enum;

/**
 * Statut d'une entrée du carnet.
 */
enum EntryStatus: string
{
    case DRAFT = 'draft';
    case PLANNED = 'planned';
    case PUBLISHED = 'published';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Brouillon',
            self::PLANNED => 'Planifié',
            self::PUBLISHED => 'Publié',
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
