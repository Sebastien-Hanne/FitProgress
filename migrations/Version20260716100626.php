<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260716100626 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE certificate (id INT AUTO_INCREMENT NOT NULL, certificate_number VARCHAR(100) NOT NULL, certification_type VARCHAR(100) NOT NULL, name VARCHAR(150) NOT NULL, file_path VARCHAR(255) NOT NULL, status VARCHAR(255) NOT NULL, uploaded_at DATETIME NOT NULL, validated_at DATETIME DEFAULT NULL, expiration_date DATE NOT NULL, coach_profile_id INT NOT NULL, admin_id INT DEFAULT NULL, INDEX IDX_219CDA4AA207654B (coach_profile_id), INDEX IDX_219CDA4A642B8210 (admin_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE coach_profile (id INT AUTO_INCREMENT NOT NULL, bio LONGTEXT DEFAULT NULL, coaching_style VARCHAR(255) DEFAULT NULL, specialties VARCHAR(150) DEFAULT NULL, location VARCHAR(150) DEFAULT NULL, experience_years SMALLINT DEFAULT NULL, certificate_status VARCHAR(255) NOT NULL, is_available TINYINT DEFAULT 0 NOT NULL, max_capacity SMALLINT DEFAULT 15 NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, user_id INT NOT NULL, UNIQUE INDEX UNIQ_3A874247A76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE coach_request (id INT AUTO_INCREMENT NOT NULL, status VARCHAR(255) NOT NULL, message LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, user_id INT NOT NULL, coach_profile_id INT NOT NULL, INDEX IDX_804767D7A76ED395 (user_id), INDEX IDX_804767D7A207654B (coach_profile_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE conversation (id INT AUTO_INCREMENT NOT NULL, created_at DATETIME NOT NULL, last_message_at DATETIME DEFAULT NULL, user_id INT NOT NULL, coach_profile_id INT NOT NULL, INDEX IDX_8A8E26E9A76ED395 (user_id), INDEX IDX_8A8E26E9A207654B (coach_profile_id), UNIQUE INDEX unique_user_coach (user_id, coach_profile_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE feedback (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(150) NOT NULL, content LONGTEXT NOT NULL, created_at DATETIME NOT NULL, coach_profile_id INT NOT NULL, user_id INT NOT NULL, session_id INT DEFAULT NULL, INDEX IDX_D2294458A207654B (coach_profile_id), INDEX IDX_D2294458A76ED395 (user_id), INDEX IDX_D2294458613FECDF (session_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE goal (id INT AUTO_INCREMENT NOT NULL, height_cm INT NOT NULL, initial_weight NUMERIC(5, 2) NOT NULL, target_weight NUMERIC(5, 2) NOT NULL, birth_date DATE NOT NULL, gender VARCHAR(255) DEFAULT NULL, hydration_goal_ml INT DEFAULT 2000 NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, user_id INT NOT NULL, UNIQUE INDEX UNIQ_FCDCEB2EA76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE journal_entry (id INT AUTO_INCREMENT NOT NULL, date DATE NOT NULL, weight NUMERIC(5, 2) DEFAULT NULL, bmi NUMERIC(4, 2) DEFAULT NULL, water_intake_ml INT DEFAULT NULL, steps INT DEFAULT NULL, energy_level SMALLINT DEFAULT NULL, mood VARCHAR(255) DEFAULT NULL, sleep_hours NUMERIC(4, 2) DEFAULT NULL, sleep_quality SMALLINT DEFAULT NULL, notes LONGTEXT DEFAULT NULL, coach_comment LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, user_id INT NOT NULL, INDEX IDX_C8FAAE5AA76ED395 (user_id), UNIQUE INDEX unique_user_date (user_id, date), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE meal (id INT AUTO_INCREMENT NOT NULL, type VARCHAR(255) NOT NULL, title VARCHAR(150) NOT NULL, calories INT DEFAULT NULL, created_at DATETIME NOT NULL, journal_entry_id INT NOT NULL, INDEX IDX_9EF68E9C6A86E4FB (journal_entry_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE message (id INT AUTO_INCREMENT NOT NULL, content VARCHAR(1000) NOT NULL, is_read TINYINT DEFAULT 0 NOT NULL, sent_at DATETIME NOT NULL, created_at DATETIME NOT NULL, conversation_id INT NOT NULL, sender_id INT NOT NULL, INDEX IDX_B6BD307F9AC0396 (conversation_id), INDEX IDX_B6BD307FF624B39D (sender_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE notification (id INT AUTO_INCREMENT NOT NULL, type VARCHAR(255) NOT NULL, title VARCHAR(150) NOT NULL, content LONGTEXT NOT NULL, is_read TINYINT DEFAULT 0 NOT NULL, created_at DATETIME NOT NULL, user_id INT NOT NULL, INDEX IDX_BF5476CAA76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE session (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(150) NOT NULL, start_at DATETIME NOT NULL, duration_minutes SMALLINT DEFAULT 60 NOT NULL, status VARCHAR(255) DEFAULT \'scheduled\' NOT NULL, notes LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, coach_profile_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_D044D5D4A207654B (coach_profile_id), INDEX IDX_D044D5D4A76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE `user` (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, proxy_email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, name VARCHAR(100) NOT NULL, photo VARCHAR(255) DEFAULT NULL, reset_token VARCHAR(255) DEFAULT NULL, reset_token_at DATETIME DEFAULT NULL, is_deleted TINYINT DEFAULT 0 NOT NULL, is_profile_visible TINYINT DEFAULT 1 NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), UNIQUE INDEX UNIQ_8D93D6493EEED659 (proxy_email), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE certificate ADD CONSTRAINT FK_219CDA4AA207654B FOREIGN KEY (coach_profile_id) REFERENCES coach_profile (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE certificate ADD CONSTRAINT FK_219CDA4A642B8210 FOREIGN KEY (admin_id) REFERENCES `user` (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE coach_profile ADD CONSTRAINT FK_3A874247A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE coach_request ADD CONSTRAINT FK_804767D7A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE coach_request ADD CONSTRAINT FK_804767D7A207654B FOREIGN KEY (coach_profile_id) REFERENCES coach_profile (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE conversation ADD CONSTRAINT FK_8A8E26E9A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE conversation ADD CONSTRAINT FK_8A8E26E9A207654B FOREIGN KEY (coach_profile_id) REFERENCES coach_profile (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE feedback ADD CONSTRAINT FK_D2294458A207654B FOREIGN KEY (coach_profile_id) REFERENCES coach_profile (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE feedback ADD CONSTRAINT FK_D2294458A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE feedback ADD CONSTRAINT FK_D2294458613FECDF FOREIGN KEY (session_id) REFERENCES session (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE goal ADD CONSTRAINT FK_FCDCEB2EA76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE journal_entry ADD CONSTRAINT FK_C8FAAE5AA76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE meal ADD CONSTRAINT FK_9EF68E9C6A86E4FB FOREIGN KEY (journal_entry_id) REFERENCES journal_entry (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307F9AC0396 FOREIGN KEY (conversation_id) REFERENCES conversation (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307FF624B39D FOREIGN KEY (sender_id) REFERENCES `user` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CAA76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE session ADD CONSTRAINT FK_D044D5D4A207654B FOREIGN KEY (coach_profile_id) REFERENCES coach_profile (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE session ADD CONSTRAINT FK_D044D5D4A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE certificate DROP FOREIGN KEY FK_219CDA4AA207654B');
        $this->addSql('ALTER TABLE certificate DROP FOREIGN KEY FK_219CDA4A642B8210');
        $this->addSql('ALTER TABLE coach_profile DROP FOREIGN KEY FK_3A874247A76ED395');
        $this->addSql('ALTER TABLE coach_request DROP FOREIGN KEY FK_804767D7A76ED395');
        $this->addSql('ALTER TABLE coach_request DROP FOREIGN KEY FK_804767D7A207654B');
        $this->addSql('ALTER TABLE conversation DROP FOREIGN KEY FK_8A8E26E9A76ED395');
        $this->addSql('ALTER TABLE conversation DROP FOREIGN KEY FK_8A8E26E9A207654B');
        $this->addSql('ALTER TABLE feedback DROP FOREIGN KEY FK_D2294458A207654B');
        $this->addSql('ALTER TABLE feedback DROP FOREIGN KEY FK_D2294458A76ED395');
        $this->addSql('ALTER TABLE feedback DROP FOREIGN KEY FK_D2294458613FECDF');
        $this->addSql('ALTER TABLE goal DROP FOREIGN KEY FK_FCDCEB2EA76ED395');
        $this->addSql('ALTER TABLE journal_entry DROP FOREIGN KEY FK_C8FAAE5AA76ED395');
        $this->addSql('ALTER TABLE meal DROP FOREIGN KEY FK_9EF68E9C6A86E4FB');
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY FK_B6BD307F9AC0396');
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY FK_B6BD307FF624B39D');
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CAA76ED395');
        $this->addSql('ALTER TABLE session DROP FOREIGN KEY FK_D044D5D4A207654B');
        $this->addSql('ALTER TABLE session DROP FOREIGN KEY FK_D044D5D4A76ED395');
        $this->addSql('DROP TABLE certificate');
        $this->addSql('DROP TABLE coach_profile');
        $this->addSql('DROP TABLE coach_request');
        $this->addSql('DROP TABLE conversation');
        $this->addSql('DROP TABLE feedback');
        $this->addSql('DROP TABLE goal');
        $this->addSql('DROP TABLE journal_entry');
        $this->addSql('DROP TABLE meal');
        $this->addSql('DROP TABLE message');
        $this->addSql('DROP TABLE notification');
        $this->addSql('DROP TABLE session');
        $this->addSql('DROP TABLE `user`');
    }
}
