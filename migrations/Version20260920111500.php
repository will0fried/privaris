<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Crée la table entry_image : galerie d'images par entrée du carnet (upload via l'admin).
 */
final class Version20260920111500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Crée la table entry_image (images de contenu liées à une entrée).';
    }

    private function isSqlite(): bool
    {
        return str_contains(strtolower($this->connection->getDatabasePlatform()::class), 'sqlite');
    }

    public function up(Schema $schema): void
    {
        if ($this->isSqlite()) {
            $this->addSql('CREATE TABLE entry_image (
                id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
                entry_id INTEGER NOT NULL,
                filename VARCHAR(255) NOT NULL,
                legende VARCHAR(200) DEFAULT NULL,
                position INTEGER DEFAULT 0 NOT NULL,
                CONSTRAINT FK_entry_image_entry FOREIGN KEY (entry_id) REFERENCES "entry" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )');
            $this->addSql('CREATE INDEX IDX_entry_image_entry ON entry_image (entry_id)');

            return;
        }

        $this->addSql('CREATE TABLE entry_image (
            id INT AUTO_INCREMENT NOT NULL,
            entry_id INT NOT NULL,
            filename VARCHAR(255) NOT NULL,
            legende VARCHAR(200) DEFAULT NULL,
            position INT DEFAULT 0 NOT NULL,
            INDEX IDX_entry_image_entry (entry_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE entry_image ADD CONSTRAINT FK_entry_image_entry FOREIGN KEY (entry_id) REFERENCES `entry` (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE entry_image');
    }
}
