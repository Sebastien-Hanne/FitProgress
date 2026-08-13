<?php

namespace App\Tests\Controller;

use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class ProfileControllerTest extends WebTestCase
{
    public function testProfileRequiresAuthentication(): void
    {
        $client = static::createClient();
        $client->request('GET', '/profile');

        self::assertResponseRedirects('/login');
    }

    #[DataProvider('protectedProfilePages')]
    public function testProfileSettingsRequireAuthentication(string $path): void
    {
        $client = static::createClient();
        $client->request('GET', $path);

        self::assertResponseRedirects('/login');
    }

    public static function protectedProfilePages(): iterable
    {
        yield 'account settings' => ['/profile/account'];
        yield 'privacy' => ['/profile/privacy'];
        yield 'accessibility' => ['/accessibility'];
    }
}
