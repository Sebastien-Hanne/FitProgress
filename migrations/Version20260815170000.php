<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260815170000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Crée un profil disponible pour les comptes coach qui en sont dépourvus.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            INSERT INTO coach_profile (user_id, bio, coaching_style, specialties, location, experience_years, certificate_status, is_available, max_capacity, created_at, updated_at)
            SELECT u.id, 'Coach FitProgress disponible pour vous accompagner vers vos objectifs.', NULL, 'Coaching sportif', NULL, NULL, 'pending', 1, 15, NOW(), NOW()
            FROM `user` u
            LEFT JOIN coach_profile cp ON cp.user_id = u.id
            WHERE cp.id IS NULL AND JSON_CONTAINS(u.roles, '"ROLE_COACH"') = 1
            SQL);
    }

    public function down(Schema $schema): void
    {
        // Les profils peuvent avoir été enrichis après leur création : ne pas les supprimer.
    }
}
