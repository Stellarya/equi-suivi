<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260523133730 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE ranch ADD owner_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE ranch ADD CONSTRAINT FK_895AE3F07E3C61F9 FOREIGN KEY (owner_id) REFERENCES "app_user" (id) NOT DEFERRABLE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_895AE3F07E3C61F9 ON ranch (owner_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE ranch DROP CONSTRAINT FK_895AE3F07E3C61F9');
        $this->addSql('DROP INDEX UNIQ_895AE3F07E3C61F9');
        $this->addSql('ALTER TABLE ranch DROP owner_id');
    }
}
