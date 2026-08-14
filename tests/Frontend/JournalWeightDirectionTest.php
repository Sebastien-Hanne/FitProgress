<?php

namespace App\Tests\Frontend;

use PHPUnit\Framework\TestCase;

final class JournalWeightDirectionTest extends TestCase
{
    public function testHorizontalWeightDirectionRemainsNatural(): void
    {
        $journalPage = file_get_contents(__DIR__.'/../../assets/journal_page.js');

        self::assertIsString($journalPage);
        self::assertStringContainsString(
            'weightFromHorizontalDrag(dragStartWeight, dragStartX, event.clientX)',
            $journalPage,
            'Le script de la page doit aussi augmenter le poids vers la droite.',
        );
        self::assertStringContainsString(
            'return startWeight + steps * 0.1;',
            $journalPage,
            'La fonction centrale doit conserver le signe naturel du déplacement.',
        );
        self::assertStringContainsString('? event.deltaX > 0', $journalPage);
    }
}
