<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260807185247 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE blocs CHANGE content content LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE blocs ADD CONSTRAINT FK_90770F74C4663E4 FOREIGN KEY (page_id) REFERENCES pages (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE blocs DROP FOREIGN KEY FK_90770F74C4663E4');
        $this->addSql('ALTER TABLE blocs CHANGE content content JSON NOT NULL');
    }
}
