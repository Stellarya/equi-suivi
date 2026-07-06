<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260525182355 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE competition ADD location_id INT NOT NULL');
        $this->addSql('ALTER TABLE competition DROP location');
        $this->addSql('ALTER TABLE competition ADD CONSTRAINT FK_B50A2CB164D218E FOREIGN KEY (location_id) REFERENCES ranch (id) NOT DEFERRABLE');
        $this->addSql('CREATE INDEX IDX_B50A2CB164D218E ON competition (location_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE competition DROP CONSTRAINT FK_B50A2CB164D218E');
        $this->addSql('DROP INDEX IDX_B50A2CB164D218E');
        $this->addSql('ALTER TABLE competition ADD location VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE competition DROP location_id');
    }
}
