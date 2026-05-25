<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260525125934 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE ranch ADD department_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE ranch ADD CONSTRAINT FK_895AE3F0AE80F5DF FOREIGN KEY (department_id) REFERENCES department (id)');
        $this->addSql('CREATE INDEX IDX_895AE3F0AE80F5DF ON ranch (department_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE ranch DROP CONSTRAINT FK_895AE3F0AE80F5DF');
        $this->addSql('DROP INDEX IDX_895AE3F0AE80F5DF');
        $this->addSql('ALTER TABLE ranch DROP department_id');
    }
}
