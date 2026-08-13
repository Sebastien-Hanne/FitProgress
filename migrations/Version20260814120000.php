<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260814120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Synchronize email and password security timestamps with the current Doctrine mapping.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE `user` CHANGE password_changed_at password_changed_at DATETIME DEFAULT NULL, CHANGE email_change_expires_at email_change_expires_at DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE `user` CHANGE password_changed_at password_changed_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE email_change_expires_at email_change_expires_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
    }
}
