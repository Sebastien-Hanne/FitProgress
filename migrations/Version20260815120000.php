<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260815120000 extends AbstractMigration
{
    public function getDescription(): string { return 'Add target date and goal history'; }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE goal ADD target_date DATE DEFAULT NULL');
        $this->addSql('CREATE TABLE goal_history (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, target_weight NUMERIC(5, 2) NOT NULL, target_date DATE DEFAULT NULL, archived_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_28A19A09A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE goal_history ADD CONSTRAINT FK_28A19A09A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE goal_history');
        $this->addSql('ALTER TABLE goal DROP target_date');
    }
}
