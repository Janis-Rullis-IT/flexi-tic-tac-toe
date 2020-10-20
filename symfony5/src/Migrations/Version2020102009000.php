<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version2020102009000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '#18 move: Add the symbol field..';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');
        $this->addSql("ALTER TABLE `move` ADD `symbol` ENUM('x','o') NOT NULL DEFAULT 'x' COMMENT 'x/o'");
    }

    public function down(Schema $schema): void
    {
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');
        $this->addSql("ALTER TABLE `move` DROP `symbol`");
    }
}
