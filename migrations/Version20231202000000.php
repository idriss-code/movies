<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Add download_link field to movie table
 */
final class Version20231202000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add download_link field to movie table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE movie ADD download_link VARCHAR(500) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE movie DROP download_link');
    }
}