<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240207064415 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE telegram_message (id BIGINT AUTO_INCREMENT NOT NULL, user BIGINT DEFAULT NULL, recipient BIGINT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, chat_id BIGINT NOT NULL, message_id BIGINT NOT NULL, callback VARCHAR(255) DEFAULT NULL, text VARCHAR(255) DEFAULT NULL, INDEX IDX_EDFB51898D93D649 (user), INDEX IDX_EDFB51896804FB49 (recipient), INDEX chat_message_idx (chat_id, message_id), INDEX chat_recipient_idx (chat_id, recipient), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE telegram_user (id BIGINT AUTO_INCREMENT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, telegram_id BIGINT NOT NULL, is_bot TINYINT(1) NOT NULL, first_name VARCHAR(255) DEFAULT NULL, last_name VARCHAR(255) DEFAULT NULL, username VARCHAR(255) DEFAULT NULL, language_code VARCHAR(10) DEFAULT NULL, can_join_groups TINYINT(1) DEFAULT NULL, can_read_all_group_messages TINYINT(1) DEFAULT NULL, supports_inline_queries TINYINT(1) DEFAULT NULL, phone_number VARCHAR(255) DEFAULT NULL, location VARCHAR(255) DEFAULT NULL, INDEX iser_id_idx (telegram_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE telegram_message ADD CONSTRAINT FK_EDFB51898D93D649 FOREIGN KEY (user) REFERENCES telegram_user (id)');
        $this->addSql('ALTER TABLE telegram_message ADD CONSTRAINT FK_EDFB51896804FB49 FOREIGN KEY (recipient) REFERENCES telegram_user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE telegram_message DROP FOREIGN KEY FK_EDFB51898D93D649');
        $this->addSql('ALTER TABLE telegram_message DROP FOREIGN KEY FK_EDFB51896804FB49');
        $this->addSql('DROP TABLE telegram_message');
        $this->addSql('DROP TABLE telegram_user');
    }
}
