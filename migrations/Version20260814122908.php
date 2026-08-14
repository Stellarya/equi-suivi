<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260814122908 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE protocol ADD status VARCHAR(20) DEFAULT \'uploaded\' NOT NULL');
        $this->addSql('ALTER TABLE protocol ALTER raw_text DROP NOT NULL');
        $this->addSql('ALTER TABLE protocol ALTER total_points TYPE NUMERIC(6, 2)');
        $this->addSql('ALTER TABLE protocol ALTER max_points TYPE NUMERIC(6, 2)');
        $this->addSql('ALTER TABLE protocol ALTER max_points DROP NOT NULL');
        $this->addSql('ALTER TABLE protocol ALTER percentage TYPE NUMERIC(6, 3)');
        $this->addSql('ALTER TABLE protocol ALTER general_comment TYPE TEXT');
        $this->addSql('ALTER TABLE protocol_figure_score ALTER score TYPE NUMERIC(4, 2)');
        $this->addSql('ALTER TABLE protocol_figure_score ALTER comment TYPE TEXT');
        $this->addSql('ALTER TABLE protocol_figure_score ALTER final_score TYPE NUMERIC(5, 2)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE protocol DROP status');
        $this->addSql('ALTER TABLE protocol ALTER raw_text SET NOT NULL');
        $this->addSql('ALTER TABLE protocol ALTER total_points TYPE INT');
        $this->addSql('ALTER TABLE protocol ALTER max_points TYPE INT');
        $this->addSql('ALTER TABLE protocol ALTER max_points SET NOT NULL');
        $this->addSql('ALTER TABLE protocol ALTER percentage TYPE INT');
        $this->addSql('ALTER TABLE protocol ALTER general_comment TYPE VARCHAR(255)');
        $this->addSql('ALTER TABLE protocol_figure_score ALTER score TYPE INT');
        $this->addSql('ALTER TABLE protocol_figure_score ALTER comment TYPE VARCHAR(255)');
        $this->addSql('ALTER TABLE protocol_figure_score ALTER final_score TYPE INT');
    }
}
