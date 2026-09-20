<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Crée la table constat et ajoute entry.echantillon.
 * Le constat est la nouvelle unité du site : une affirmation datée rattachée à une entrée.
 */
final class Version20260920162651 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Crée la table constat + entry.echantillon.';
    }

    private function isSqlite(): bool
    {
        return str_contains(strtolower($this->connection->getDatabasePlatform()::class), 'sqlite');
    }

    public function up(Schema $schema): void
    {
        if ($this->isSqlite()) {
            $this->addSql('CREATE TABLE constat (
                id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
                entry_id INTEGER NOT NULL,
                reference VARCHAR(20) NOT NULL,
                enonce CLOB NOT NULL,
                majeur BOOLEAN DEFAULT 0 NOT NULL,
                date_publication DATE DEFAULT NULL,
                retire BOOLEAN DEFAULT 0 NOT NULL,
                CONSTRAINT FK_constat_entry FOREIGN KEY (entry_id) REFERENCES "entry" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )');
            $this->addSql('CREATE UNIQUE INDEX UNIQ_constat_reference ON constat (reference)');
            $this->addSql('CREATE INDEX IDX_constat_entry ON constat (entry_id)');
            $this->addSql('ALTER TABLE entry ADD COLUMN echantillon VARCHAR(60) DEFAULT NULL');

            return;
        }

        $this->addSql('CREATE TABLE constat (
            id INT AUTO_INCREMENT NOT NULL,
            entry_id INT NOT NULL,
            reference VARCHAR(20) NOT NULL,
            enonce LONGTEXT NOT NULL,
            majeur TINYINT(1) DEFAULT 0 NOT NULL,
            date_publication DATE DEFAULT NULL,
            retire TINYINT(1) DEFAULT 0 NOT NULL,
            UNIQUE INDEX UNIQ_constat_reference (reference),
            INDEX IDX_constat_entry (entry_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE constat ADD CONSTRAINT FK_constat_entry FOREIGN KEY (entry_id) REFERENCES `entry` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE entry ADD echantillon VARCHAR(60) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE constat');
        $this->addSql('ALTER TABLE entry DROP COLUMN echantillon');
    }
}
