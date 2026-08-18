<?php

namespace App\Tests\Controller;

use App\Entity\Goal;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class OnboardingAccessTest extends WebTestCase
{
    #[DataProvider('protectedUserRoutes')]
    public function testIncompleteUserIsRedirectedToOnboarding(string $path): void
    {
        $client = self::createClient();
        $user = $this->createUser();
        $client->loginUser($user);

        $client->request('GET', $path);

        self::assertResponseRedirects('/bienvenue/questionnaire');
    }

    public static function protectedUserRoutes(): iterable
    {
        yield 'dashboard' => ['/dashboard'];
        yield 'journal' => ['/journal'];
        yield 'journal history' => ['/journal/history'];
        yield 'profile' => ['/profile'];
        yield 'account settings' => ['/profile/account'];
        yield 'privacy settings' => ['/profile/privacy'];
    }

    #[DataProvider('completedUserRoutes')]
    public function testCompletedUserKeepsNormalAccess(string $path): void
    {
        $client = self::createClient();
        $user = $this->createUser(true);
        $client->loginUser($user);

        $client->request('GET', $path);

        self::assertResponseIsSuccessful();
    }

    public static function completedUserRoutes(): iterable
    {
        yield 'dashboard' => ['/dashboard'];
        yield 'journal' => ['/journal'];
        yield 'profile' => ['/profile'];
    }

    public function testOnboardingRemainsAccessibleWithoutRedirectLoop(): void
    {
        $client = self::createClient();
        $client->loginUser($this->createUser());

        $client->request('GET', '/bienvenue/questionnaire');

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('form[name="onboarding_goal"]');
    }

    public function testLogoutRemainsAccessible(): void
    {
        $client = self::createClient();
        $client->loginUser($this->createUser());

        $client->request('GET', '/logout');

        self::assertResponseRedirects('/');
    }

    #[DataProvider('publicLegalRoutes')]
    public function testLegalPagesRemainPublic(string $path): void
    {
        $client = self::createClient();

        $client->request('GET', $path);

        self::assertResponseIsSuccessful();
    }

    public static function publicLegalRoutes(): iterable
    {
        yield 'terms' => ['/conditions-utilisation'];
        yield 'privacy' => ['/politique-confidentialite'];
    }

    #[DataProvider('otherRoles')]
    public function testOtherRolesWithoutGoalAreNotRedirectedToUserOnboarding(array $roles, string $path): void
    {
        $client = self::createClient();
        $client->loginUser($this->createUser(false, $roles));

        $client->request('GET', $path);

        self::assertResponseIsSuccessful();
    }

    public static function otherRoles(): iterable
    {
        yield 'coach' => [['ROLE_COACH'], '/coach/dashboard'];
        yield 'admin' => [['ROLE_ADMIN'], '/admin/dashboard'];
    }

    private function createUser(bool $withGoal = false, array $roles = ['ROLE_USER']): User
    {
        $suffix = bin2hex(random_bytes(6));
        $user = (new User())
            ->setEmail('onboarding-'.$suffix.'@example.test')
            ->setProxyEmail('proxy-onboarding-'.$suffix.'@example.test')
            ->setName('Onboarding Test')
            ->setPassword('not-used-by-loginUser')
            ->setRoles($roles);

        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $entityManager->persist($user);
        if ($withGoal) {
            $goal = (new Goal())
                ->setUser($user)
                ->setHeightCm(175)
                ->setInitialWeight('80.0')
                ->setTargetWeight('72.0')
                ->setBirthDate(new \DateTimeImmutable('1992-01-01'));
            $user->setGoal($goal);
            $entityManager->persist($goal);
        }
        $entityManager->flush();

        return $user;
    }
}
