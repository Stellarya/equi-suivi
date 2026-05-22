<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260522185006 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE horse_discipline (horse_id INT NOT NULL, discipline_id INT NOT NULL, PRIMARY KEY (horse_id, discipline_id))');
        $this->addSql('CREATE INDEX IDX_71E30CF276B275AD ON horse_discipline (horse_id)');
        $this->addSql('CREATE INDEX IDX_71E30CF2A5522701 ON horse_discipline (discipline_id)');
        $this->addSql('ALTER TABLE horse_discipline ADD CONSTRAINT FK_71E30CF276B275AD FOREIGN KEY (horse_id) REFERENCES horse (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE horse_discipline ADD CONSTRAINT FK_71E30CF2A5522701 FOREIGN KEY (discipline_id) REFERENCES discipline (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE horse_discipline DROP CONSTRAINT FK_71E30CF276B275AD');
        $this->addSql('ALTER TABLE horse_discipline DROP CONSTRAINT FK_71E30CF2A5522701');
        $this->addSql('DROP TABLE horse_discipline');
    }
}
