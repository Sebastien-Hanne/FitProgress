<?php

namespace App\Tests\Controller;

use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class CoachMatchingControllerTest extends WebTestCase
{
    #[DataProvider('protectedPages')]
    public function testPagesRequireAuthentication(string $path): void
    {
        $client = static::createClient();
        $client->request('GET', $path);

        self::assertResponseRedirects('/login');
    }

    public static function protectedPages(): iterable
    {
        yield 'coach list' => ['/choisir-coach'];
        yield 'request status' => ['/choisir-coach/ma-demande'];
    }
}
