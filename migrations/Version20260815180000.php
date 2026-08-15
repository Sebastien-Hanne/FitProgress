<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260815180000 extends AbstractMigration
{
    public function getDescription(): string { return 'Ajoute le titre des conversations internes.'; }
    public function up(Schema $schema): void { $this->addSql("ALTER TABLE conversation ADD title VARCHAR(150) DEFAULT 'Accompagnement' NOT NULL"); }
    public function down(Schema $schema): void { $this->addSql('ALTER TABLE conversation DROP title'); }
}
