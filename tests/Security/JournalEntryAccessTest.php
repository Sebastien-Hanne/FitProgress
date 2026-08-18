<?php

namespace App\Tests\Security;

use App\Entity\JournalEntry;
use App\Entity\Goal;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class JournalEntryAccessTest extends WebTestCase
{
    public function testUserCannotReadUpdateOrDeleteAnotherUsersJournalEntry(): void
    {
        $client = self::createClient();
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $suffix = bin2hex(random_bytes(6));

        $userA = $this->createUser('user-a-'.$suffix.'@example.test');
        $userB = $this->createUser('user-b-'.$suffix.'@example.test');
        $entry = (new JournalEntry())
            ->setUser($userA)
            ->setDate(new \DateTimeImmutable('today'))
            ->setWeight('81.50')
            ->setNotes('private-entry-'.$suffix);

        $entityManager->persist($userA);
        $entityManager->persist($userB);
        $entityManager->persist($userA->getGoal());
        $entityManager->persist($userB->getGoal());
        $entityManager->persist($entry);
        $entityManager->flush();

        $entryId = $entry->getId();
        $userAId = $userA->getId();
        self::assertNotNull($entryId);
        self::assertNotNull($userAId);

        $client->loginUser($userB);

        $client->request('GET', '/journal/history');
        self::assertResponseIsSuccessful();
        self::assertStringNotContainsString('private-entry-'.$suffix, $client->getResponse()->getContent() ?: '');

        $client->request('GET', sprintf('/journal/%d', $entryId));
        self::assertResponseStatusCodeSame(404);

        $client->request('GET', sprintf('/journal/%d/edit', $entryId));
        self::assertResponseStatusCodeSame(404);
        $this->assertEntryIsUnchanged($entryId, $userAId, $suffix);

        $client->request('POST', sprintf('/journal/%d/edit', $entryId), [
            'journal_entry' => [
                'weight' => '55.00',
                'notes' => 'idor-update-attempt',
            ],
        ]);
        self::assertResponseStatusCodeSame(404);
        $this->assertEntryIsUnchanged($entryId, $userAId, $suffix);

        $client->request('POST', sprintf('/journal/%d/delete', $entryId), [
            '_token' => 'invalid-token-is-irrelevant-before-ownership-check',
        ]);
        self::assertResponseStatusCodeSame(404);
        $this->assertEntryIsUnchanged($entryId, $userAId, $suffix);
    }

    private function createUser(string $email): User
    {
        $user = (new User())
            ->setEmail($email)
            ->setProxyEmail('proxy-'.$email)
            ->setName($email)
            ->setPassword('not-used-by-loginUser')
            ->setRoles(['ROLE_USER']);
        $goal = (new Goal())
            ->setUser($user)
            ->setHeightCm(175)
            ->setInitialWeight('80.0')
            ->setTargetWeight('72.0')
            ->setBirthDate(new \DateTimeImmutable('1992-01-01'));
        $user->setGoal($goal);

        return $user;
    }

    private function assertEntryIsUnchanged(int $entryId, int $userAId, string $suffix): void
    {
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $entityManager->clear();

        $entry = $entityManager->find(JournalEntry::class, $entryId);

        self::assertNotNull($entry, 'L’entrée de l’utilisateur A ne doit pas être supprimée.');
        self::assertSame($userAId, $entry->getUser()?->getId());
        self::assertEqualsWithDelta(81.50, (float) $entry->getWeight(), 0.001);
        self::assertSame('private-entry-'.$suffix, $entry->getNotes());
    }
}
