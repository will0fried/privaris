<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Ajoute la colonne share_image à entry : image de partage (og:image / twitter:image) propre à chaque entrée.
 */
final class Version20260920110135 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajoute entry.share_image (image de partage réseaux sociaux, nullable).';
    }

    public function up(Schema $schema): void
    {
        // Compatible SQLite (dev) et MySQL/MariaDB (prod).
        $this->addSql('ALTER TABLE entry ADD COLUMN share_image VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE entry DROP COLUMN share_image');
    }
}
