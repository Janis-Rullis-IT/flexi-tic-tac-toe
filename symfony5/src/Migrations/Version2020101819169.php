<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version2020101819169 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '#14 game: allow the move_cnt_to_win to be null..';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');
        $this->addSql("ALTER TABLE `game` CHANGE `move_cnt_to_win` `move_cnt_to_win` TINYINT UNSIGNED NULL DEFAULT NULL COMMENT 'Must be no smaller than the min board dimensions or go outside the board. #14 #15'");
    }

    public function down(Schema $schema): void
    {
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');
        $this->addSql("ALTER TABLE `game` CHANGE `move_cnt_to_win` TINYINT UNSIGNED NOT NULL DEFAULT 3 COMMENT 'Must be no smaller than the min board dimensions or go outside the board. #14 #15'");
    }
}
