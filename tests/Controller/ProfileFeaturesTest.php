<?php

namespace App\Tests\Controller;

use App\Entity\Goal;
use App\Entity\JournalEntry;
use App\Entity\Notification;
use App\Entity\User;
use App\Enum\Gender;
use App\Enum\NotificationType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class ProfileFeaturesTest extends WebTestCase
{
    public function testUserCanUpdateAccountAndPhysicalProfile(): void
    {
        $client = self::createClient();
        [$user] = $this->createUserWithGoal();
        $userId = $user->getId();
        $client->loginUser($user);

        $crawler = $client->request('GET', '/profile/account');
        self::assertResponseIsSuccessful();
        $form = $crawler->selectButton('Enregistrer les modifications')->form([
            'profile_account[name]' => 'Nom Modifié',
            'profile_account[email]' => 'modified-'.bin2hex(random_bytes(5)).'@example.test',
            'profile_account[phone]' => '+33 6 12 34 56 78',
            'profile_account[heightCm]' => '182',
            'profile_account[targetWeight]' => '76.0',
            'profile_account[birthDate]' => '1990-05-12',
            'profile_account[gender]' => Gender::Male->value,
        ]);
        $client->submit($form);

        self::assertResponseRedirects('/profile/account');
        $updatedUser = self::getContainer()->get(EntityManagerInterface::class)->find(User::class, $userId);
        self::assertSame('Nom Modifié', $updatedUser?->getName());
        self::assertSame('+33 6 12 34 56 78', $updatedUser?->getPhone());
        self::assertSame(182, $updatedUser?->getGoal()?->getHeightCm());
        self::assertSame(80.0, (float) $updatedUser?->getGoal()?->getInitialWeight());
        self::assertSame(Gender::Male, $updatedUser?->getGoal()?->getGender());
        self::assertFalse($crawler->filter('#profile_account_initialWeight')->count() > 0);
    }

    public function testAddingAJournalWeightDoesNotChangeInitialWeight(): void
    {
        $client = self::createClient();
        [$user] = $this->createUserWithGoal();
        $userId = $user->getId();
        $client->loginUser($user);

        $crawler = $client->request('GET', '/journal');
        $client->submit($crawler->selectButton('Enregistrer')->form([
            'journal_entry[weight]' => '75.0',
        ]));

        self::assertResponseRedirects('/journal');
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $entityManager->clear();
        $updatedUser = $entityManager->find(User::class, $userId);
        self::assertSame(80.0, (float) $updatedUser?->getGoal()?->getInitialWeight());
    }

    public function testProfileDisplaysLatestJournalWeightAsCurrentWeight(): void
    {
        $client = self::createClient();
        [$user] = $this->createUserWithGoal();
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $entityManager->persist((new JournalEntry())->setUser($user)->setDate(new \DateTimeImmutable('2026-08-12'))->setWeight('128.0'));
        $entityManager->persist((new JournalEntry())->setUser($user)->setDate(new \DateTimeImmutable('2026-08-13'))->setWeight('125.0'));
        $entityManager->flush();
        $client->loginUser($user);

        $client->request('GET', '/profile');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('.profile-physical', '125,0 kg');
        self::assertSelectorTextNotContains('.profile-physical', '128,0 kg');
    }

    public function testProfileWithoutJournalWeightShowsEmptyStateAndJournalLink(): void
    {
        $client = self::createClient();
        [$user] = $this->createUserWithGoal();
        $client->loginUser($user);

        $client->request('GET', '/profile/account');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('body', 'Aucune pesée');
        self::assertSelectorExists('a[href="/journal"]');
        self::assertSelectorTextContains('a[href="/journal"]', 'Ajouter ma première pesée');
    }

    public function testNotificationsArePrivateAndCanBeMarkedAsRead(): void
    {
        $client = self::createClient();
        [$user] = $this->createUserWithGoal();
        $notification = (new Notification())
            ->setUser($user)
            ->setType(NotificationType::new_feedback)
            ->setTitle('Nouveau retour du coach')
            ->setContent('Votre coach a ajouté un commentaire.');
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $entityManager->persist($notification);
        $entityManager->flush();
        $notificationId = $notification->getId();
        $client->loginUser($user);

        $crawler = $client->request('GET', '/notifications');
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('body', 'Nouveau retour du coach');
        $client->submit($crawler->selectButton('Tout marquer comme lu')->form());
        self::assertResponseRedirects('/notifications');
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $updatedNotification = $entityManager->find(Notification::class, $notificationId);
        self::assertTrue($updatedNotification?->isRead());
    }

    public function testAccountDeletionRequiresPasswordAndDeletesUser(): void
    {
        $client = self::createClient();
        [$user, $plainPassword] = $this->createUserWithGoal(true);
        $userId = $user->getId();
        $client->loginUser($user);

        $crawler = $client->request('GET', '/profile/privacy');
        $form = $crawler->selectButton('Supprimer définitivement mon compte')->form(['password' => 'wrong-password']);
        $client->submit($form);
        self::assertResponseRedirects('/profile/privacy');
        self::assertNotNull(self::getContainer()->get(EntityManagerInterface::class)->find(User::class, $userId));

        $crawler = $client->followRedirect();
        $form = $crawler->selectButton('Supprimer définitivement mon compte')->form(['password' => $plainPassword]);
        $client->submit($form);
        self::assertResponseRedirects('/login');
        self::getContainer()->get(EntityManagerInterface::class)->clear();
        self::assertNull(self::getContainer()->get(EntityManagerInterface::class)->find(User::class, $userId));
    }

    /** @return array{User, string} */
    private function createUserWithGoal(bool $hashPassword = false): array
    {
        $suffix = bin2hex(random_bytes(6));
        $plainPassword = 'Valid-test-password-42!';
        $user = (new User())
            ->setEmail('profile-'.$suffix.'@example.test')
            ->setProxyEmail('proxy-'.$suffix.'@example.test')
            ->setName('Profil Test')
            ->setRoles(['ROLE_USER']);
        $password = $hashPassword
            ? self::getContainer()->get(UserPasswordHasherInterface::class)->hashPassword($user, $plainPassword)
            : 'not-used-by-loginUser';
        $user->setPassword($password);
        $goal = (new Goal())
            ->setUser($user)
            ->setHeightCm(175)
            ->setInitialWeight('80.0')
            ->setTargetWeight('72.0')
            ->setBirthDate(new \DateTimeImmutable('1992-01-01'));
        $user->setGoal($goal);
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $entityManager->persist($user);
        $entityManager->persist($goal);
        $entityManager->flush();

        return [$user, $plainPassword];
    }
}
