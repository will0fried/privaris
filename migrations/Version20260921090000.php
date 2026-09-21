<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Ajoute entry.serie : le format sériel d'une entree (rejeu / vendu_ia), nullable.
 * Colonne nullable -> les entrees existantes restent « hors serie », rien n'est modifie.
 */
final class Version20260921090000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajoute entry.serie (format serie : rejeu / vendu_ia, nullable).';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE entry ADD COLUMN serie VARCHAR(20) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE entry DROP COLUMN serie');
    }
}
