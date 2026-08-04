<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260804150000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add physical activity duration to daily journal entries.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE journal_entry ADD activity_minutes INT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE journal_entry DROP activity_minutes');
    }
}
