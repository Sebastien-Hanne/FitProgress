<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260820213041 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Aligne les métadonnées de conversation et goal_history avec le mapping Doctrine.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE conversation CHANGE title title VARCHAR(150) NOT NULL');
        $this->addSql('ALTER TABLE goal_history CHANGE archived_at archived_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE goal_history RENAME INDEX idx_28a19a09a76ed395 TO IDX_569172B7A76ED395');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE conversation CHANGE title title VARCHAR(150) DEFAULT \'Accompagnement\' NOT NULL');
        $this->addSql('ALTER TABLE goal_history CHANGE archived_at archived_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE goal_history RENAME INDEX idx_569172b7a76ed395 TO IDX_28A19A09A76ED395');
    }
}
