<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260523141414 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE ranch DROP CONSTRAINT fk_895ae3f07e3c61f9');
        $this->addSql('DROP INDEX uniq_895ae3f07e3c61f9');
        $this->addSql('ALTER TABLE ranch RENAME COLUMN owner_id TO app_user_id');
        $this->addSql('ALTER TABLE ranch ADD CONSTRAINT FK_895AE3F04A3353D8 FOREIGN KEY (app_user_id) REFERENCES "app_user" (id) NOT DEFERRABLE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_895AE3F04A3353D8 ON ranch (app_user_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE ranch DROP CONSTRAINT FK_895AE3F04A3353D8');
        $this->addSql('DROP INDEX UNIQ_895AE3F04A3353D8');
        $this->addSql('ALTER TABLE ranch RENAME COLUMN app_user_id TO owner_id');
        $this->addSql('ALTER TABLE ranch ADD CONSTRAINT fk_895ae3f07e3c61f9 FOREIGN KEY (owner_id) REFERENCES app_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX uniq_895ae3f07e3c61f9 ON ranch (owner_id)');
    }
}
