<?php

namespace App\Tests\Controller;

use App\Entity\CoachProfile;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class CoachSpaceControllerTest extends WebTestCase
{
    #[DataProvider('coachPages')]
    public function testCoachPagesRequireAuthentication(string $path): void
    {
        $client = static::createClient();
        $client->request('GET', $path);
        self::assertResponseRedirects('/login');
    }

    public static function coachPages(): iterable
    {
        yield ['/coach/clients'];
        yield ['/coach/profil'];
        yield ['/coach/demandes'];
    }

    public function testCoachCanOpenClientListAndProfile(): void
    {
        $client = static::createClient();
        $suffix = bin2hex(random_bytes(5));
        $user = (new User())->setName('Coach Test')->setEmail("coach-$suffix@example.test")
            ->setProxyEmail("proxy-$suffix@example.test")->setPassword('unused')->setRoles(['ROLE_COACH']);
        $profile = (new CoachProfile())->setUser($user)->setSpecialties('Musculation')->setIsAvailable(true);
        $user->setCoachProfile($profile);
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $entityManager->persist($user); $entityManager->persist($profile); $entityManager->flush();
        $client->loginUser($user);

        $client->request('GET', '/coach/clients');
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Liste des Clients');
        self::assertSelectorExists('a[href="/coach/demandes"]');
        self::assertSelectorExists('a[href="/coach/feedback"]');
        self::assertSelectorExists('a[href="/coach/clients?filter=history"]');

        $client->request('GET', '/coach/clients?filter=history');
        self::assertResponseIsSuccessful();
        self::assertSelectorExists('section[aria-label="Clients avec historique"]');
        $client->request('GET', '/coach/profil');
        self::assertResponseIsSuccessful();
        self::assertSelectorExists('form[name="coach_profile"]');
        self::assertSelectorExists('header a[aria-label="Voir le profil de Coach Test"]');
        self::assertSelectorNotExists('#coach_profile_location');
        self::assertSelectorExists('a[aria-label="Modifier mon nom ou ma photo"][href="/profile/account?edit=identity"]');
        $client->request('GET', '/profile');
        self::assertResponseRedirects('/coach/profil');
        $client->request('GET', '/profile/account');
        self::assertResponseIsSuccessful();
        self::assertSelectorExists('form[name="profile_account"]');
        self::assertSelectorNotExists('#profile_account_heightCm');
        self::assertSelectorTextContains('#professional-title', 'Activité professionnelle');
        self::assertSelectorTextNotContains('body', 'Profil physique');
        self::assertSelectorExists('nav[aria-label="Navigation coach"]');

        $client->request('GET', '/notifications');
        self::assertResponseIsSuccessful();
        self::assertSelectorExists('nav[aria-label="Navigation coach"]');
        self::assertSelectorNotExists('nav[aria-label="Navigation principale"]');

        $client->request('GET', '/profile/account?edit=identity');
        self::assertResponseIsSuccessful();
        self::assertSelectorExists('#account-edit-overlay:not([hidden])');
        self::assertSelectorExists('#profile_account_name');
        self::assertSelectorExists('#profile_account_photoFile');
    }
}
