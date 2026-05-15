<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260515185607 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE type_maintenance ADD conseils TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE type_maintenance ADD mnemonique VARCHAR(50) DEFAULT \'-\' NOT NULL');
        $this->addSql('ALTER TABLE type_maintenance ADD est_actif BOOLEAN DEFAULT true NOT NULL');
        $this->addSql('ALTER TABLE type_maintenance ALTER interval_default_unit DROP NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE type_maintenance DROP conseils');
        $this->addSql('ALTER TABLE type_maintenance DROP mnemonique');
        $this->addSql('ALTER TABLE type_maintenance DROP est_actif');
        $this->addSql('ALTER TABLE type_maintenance ALTER interval_default_unit SET NOT NULL');
    }
}
