<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version2020101818369 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '#14 Create the `game` table.';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');
        $this->addSql("CREATE TABLE IF NOT EXISTS `game`(
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `status` ENUM(
        'draft',
        'completed',
        'ongoing',
        'other'
    ) NULL DEFAULT 'draft' COMMENT '#14.',
    `width` TINYINT UNSIGNED NOT NULL DEFAULT 3 COMMENT '2-20. Smaller doesnt make sense, bigger is too rought to process and play. #14 #12',
    `height` TINYINT UNSIGNED NOT NULL DEFAULT 3 COMMENT '2-20. Smaller doesnt make sense, bigger is too rought to process and play. #14 #12',
    `move_cnt_to_win` TINYINT UNSIGNED NOT NULL DEFAULT 3 COMMENT 'Must be no smaller than the min board dimensions or go outside the board. #14 #15',
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at` TIMESTAMP NULL DEFAULT NULL,
    `sys_info` VARCHAR(20) DEFAULT NULL COMMENT 'In case if You need to mark/flag or just leave a comment.',
    PRIMARY KEY(`id`),
    INDEX `status`(`status`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8 COMMENT = 'Related information in #14.'");
    }

    public function down(Schema $schema): void
    {
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');
        $this->addSql('DROP TABLE `game`;');
    }
}
