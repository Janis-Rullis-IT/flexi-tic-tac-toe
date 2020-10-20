<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version2020102009001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '#18 move: Remove the current INDEX..';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');
        $this->addSql("ALTER TABLE `move` DROP INDEX `game_id`");
    }

    public function down(Schema $schema): void
    {
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');
		$this->addSql("ALTER TABLE `move` ADD INDEX `game_id`(`game_id`)");
    }
}
