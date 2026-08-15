<?php

namespace App\Tests\Controller;

use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class MessageControllerTest extends WebTestCase
{
    #[DataProvider('protectedMessagingPages')]
    public function testMessagingRequiresAuthentication(string $path): void
    {
        $client = static::createClient();
        $client->request('GET', $path);
        self::assertResponseRedirects('/login');
    }

    public static function protectedMessagingPages(): iterable
    {
        yield 'conversation list' => ['/messages'];
        yield 'conversation thread' => ['/messages/999999'];
    }
}
