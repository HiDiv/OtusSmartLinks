<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250606152123 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE strategy (
              id BINARY(16) NOT NULL COMMENT '(DC2Type:uuid)',
              path VARCHAR(255) NOT NULL,
              priority INT NOT NULL,
              INDEX idx_strategy_path_priority (path, priority),
              PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE strategy_action (
              id BINARY(16) NOT NULL COMMENT '(DC2Type:uuid)',
              strategy_id BINARY(16) NOT NULL COMMENT '(DC2Type:uuid)',
              handler_tag VARCHAR(100) NOT NULL,
              parameters JSON DEFAULT NULL COMMENT '(DC2Type:json)',
              UNIQUE INDEX UNIQ_8BD55A38D5CAD932 (strategy_id),
              INDEX idx_action_tag (handler_tag),
              PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE strategy_condition (
              id BINARY(16) NOT NULL COMMENT '(DC2Type:uuid)',
              strategy_id BINARY(16) NOT NULL COMMENT '(DC2Type:uuid)',
              handler_tag VARCHAR(100) NOT NULL,
              parameters JSON DEFAULT NULL COMMENT '(DC2Type:json)',
              INDEX IDX_DA309A60D5CAD932 (strategy_id),
              INDEX idx_condition_tag (handler_tag),
              PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              strategy_action
            ADD
              CONSTRAINT FK_8BD55A38D5CAD932 FOREIGN KEY (strategy_id) REFERENCES strategy (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              strategy_condition
            ADD
              CONSTRAINT FK_DA309A60D5CAD932 FOREIGN KEY (strategy_id) REFERENCES strategy (id) ON DELETE CASCADE
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE strategy_action DROP FOREIGN KEY FK_8BD55A38D5CAD932
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE strategy_condition DROP FOREIGN KEY FK_DA309A60D5CAD932
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE strategy
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE strategy_action
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE strategy_condition
        SQL);
    }
}
