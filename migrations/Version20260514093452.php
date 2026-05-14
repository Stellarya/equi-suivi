<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260514093452 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE protocol_figure ADD section VARCHAR(30) NOT NULL');
        $this->addSql('ALTER TABLE protocol_figure ADD ordre SMALLINT NOT NULL');
        $this->addSql('ALTER TABLE protocol_figure ADD label VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE protocol_figure ADD max_points SMALLINT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE protocol_figure DROP section');
        $this->addSql('ALTER TABLE protocol_figure DROP ordre');
        $this->addSql('ALTER TABLE protocol_figure DROP label');
        $this->addSql('ALTER TABLE protocol_figure DROP max_points');
    }
}
