<?php

namespace App\Tests\Entity;

use App\Entity\JournalEntry;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class JournalEntryTest extends TestCase
{
    public function testRecalculateBmiFromWeightAndHeight(): void
    {
        $entry = (new JournalEntry())->setWeight('80.00');

        $entry->recalculateBmi(180);

        self::assertSame('24.69', $entry->getBmi());
    }

    #[DataProvider('invalidHeightProvider')]
    public function testBmiIsNotCalculatedWithoutAValidHeight(?int $height): void
    {
        $entry = (new JournalEntry())->setWeight('80.00');

        $entry->recalculateBmi($height);

        self::assertNull($entry->getBmi());
    }

    /** @return iterable<string, array{?int}> */
    public static function invalidHeightProvider(): iterable
    {
        yield 'taille absente' => [null];
        yield 'taille égale à zéro' => [0];
        yield 'taille négative' => [-180];
    }
}
