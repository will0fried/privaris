<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Renomme le type d'entrée « coulisses » en « veille » (aligne l'enum sur les 4 types prévus).
 * Ne touche que les lignes concernées ; sans effet s'il n'y en a aucune.
 */
final class Version20260920164733 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Type d\'entrée coulisses -> veille.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("UPDATE entry SET type = 'veille' WHERE type = 'coulisses'");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("UPDATE entry SET type = 'coulisses' WHERE type = 'veille'");
    }
}
