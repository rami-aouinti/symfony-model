<?php

declare(strict_types=1);

// phpcs:ignoreFile
namespace DoctrineMigrations;

use Doctrine\DBAL\Platforms\AbstractMySQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Override;

/**
 * Initial database structure
 */
final class Version20190222213409 extends AbstractMigration
{
    /**
     * @noinspection PhpMissingParentCallCommonInspection
     */
    #[Override]
    public function getDescription(): string
    {
        return 'Initial database structure';
    }

    /**
     * @noinspection PhpMissingParentCallCommonInspection
     */
    #[Override]
    public function isTransactional(): bool
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof AbstractMySQLPlatform,
            'Migration can only be executed safely on \'mysql\'.'
        );

        $sql = <<<SQL
CREATE TABLE scheduled_command (
    id INT AUTO_INCREMENT NOT NULL,
    name VARCHAR(150) NOT NULL,
    command VARCHAR(200) NOT NULL,
    arguments LONGTEXT DEFAULT NULL,
    cron_expression VARCHAR(200) DEFAULT NULL,
    last_execution DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime)',
    last_return_code INT DEFAULT NULL,
    log_file VARCHAR(150) DEFAULT NULL,
    priority INT NOT NULL,
    execute_immediately TINYINT(1) NOT NULL,
    disabled TINYINT(1) NOT NULL,
    locked TINYINT(1) NOT NULL,
    ping_back_url VARCHAR(255) DEFAULT NULL,
    ping_back_failed_url VARCHAR(255) DEFAULT NULL,
    notes LONGTEXT NOT NULL,
    version INT DEFAULT 1 NOT NULL,
    created_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime)',
    UNIQUE INDEX UNIQ_EA0DBC905E237E06 (name),
    PRIMARY KEY(id)
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB
SQL;

        $this->addSql($sql);

        $this->addSql('ALTER TABLE scheduled_command ');
    }

    #[Override]
    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof AbstractMySQLPlatform,
            'Migration can only be executed safely on \'mysql\'.'
        );

        $this->addSql('DROP TABLE scheduled_command');
    }
}
