<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version2020101918169 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '#18 Create the `move` table.';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');
        $this->addSql("CREATE TABLE IF NOT EXISTS `move`(
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `game_id` INT(11) UNSIGNED NOT NULL COMMENT 'id in the game table #18',    
    `row` TINYINT UNSIGNED NOT NULL COMMENT 'From 0 to boards max width or height (-1) #18',
    `column` TINYINT UNSIGNED NOT NULL COMMENT 'From 0 to boards max width or height (-1) #18',
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at` TIMESTAMP NULL DEFAULT NULL,
    `sys_info` VARCHAR(20) DEFAULT NULL COMMENT 'In case if You need to mark/flag or just leave a comment.',
    PRIMARY KEY(`id`),
    INDEX `game_id`(`game_id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8 COMMENT = 'Related information in #18.'");
    }

    public function down(Schema $schema): void
    {
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');
        $this->addSql('DROP TABLE `move`;');
    }
}
