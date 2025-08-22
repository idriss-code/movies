<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250821130143 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE movie ADD format VARCHAR(100) DEFAULT NULL, ADD file_size VARCHAR(50) DEFAULT NULL, ADD duration VARCHAR(20) DEFAULT NULL');
        $this->addSql('ALTER TABLE movie_tag RENAME INDEX idx_339af0d08f93b6fc TO IDX_DCD9F2918F93B6FC');
        $this->addSql('ALTER TABLE movie_tag RENAME INDEX idx_339af0d0bad26311 TO IDX_DCD9F291BAD26311');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE movie DROP format, DROP file_size, DROP duration');
        $this->addSql('ALTER TABLE movie_tag RENAME INDEX idx_dcd9f2918f93b6fc TO IDX_339AF0D08F93B6FC');
        $this->addSql('ALTER TABLE movie_tag RENAME INDEX idx_dcd9f291bad26311 TO IDX_339AF0D0BAD26311');
    }
}
