<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260819090000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajoute le lieu des séances de coaching';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE session ADD location VARCHAR(150) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE session DROP location');
    }
}
