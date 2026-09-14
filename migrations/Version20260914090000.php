<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Ajoute le champ menu_without_link : un élément de menu qui n'est pas
 * cliquable et sert uniquement à ouvrir son sous-menu.
 */
final class Version20260914090000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajoute menu_without_link à la table pages';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE pages ADD menu_without_link TINYINT NOT NULL');
        $this->addSql("UPDATE pages SET menu_without_link = 1 WHERE slug = 'musicienne-pianiste'");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE pages DROP menu_without_link');
    }
}
