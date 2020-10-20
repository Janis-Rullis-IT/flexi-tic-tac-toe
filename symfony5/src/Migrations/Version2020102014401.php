<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version2020102014401 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '#33 game: Add the next_symbol field..';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');
        $this->addSql("ALTER TABLE `game` ADD `next_symbol` ENUM('x','o') NOT NULL DEFAULT 'x' COMMENT 'x/o' #18 #33");
    }

    public function down(Schema $schema): void
    {
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');
        $this->addSql('ALTER TABLE `game` DROP `next_symbol`');
    }
}
