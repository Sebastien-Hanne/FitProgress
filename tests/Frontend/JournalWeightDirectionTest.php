<?php

namespace App\Tests\Frontend;

use PHPUnit\Framework\TestCase;

final class JournalWeightDirectionTest extends TestCase
{
    public function testHorizontalWeightDirectionRemainsNatural(): void
    {
        $controller = file_get_contents(__DIR__.'/../../assets/controllers/journal_entry_controller.js');

        self::assertIsString($controller);
        self::assertStringContainsString(
            'this.setWeight(this.dragStartWeight + steps * 0.1);',
            $controller,
            'Un déplacement vers la droite doit augmenter le poids.',
        );
        self::assertStringContainsString(
            '? event.deltaX > 0',
            $controller,
            'Une rotation horizontale vers la droite doit augmenter le poids.',
        );

        $journalPage = file_get_contents(__DIR__.'/../../assets/journal_page.js');

        self::assertIsString($journalPage);
        self::assertStringContainsString(
            'dragStartWeight + Math.trunc((event.clientX - dragStartX) / 12) * 0.1',
            $journalPage,
            'Le script de la page doit aussi augmenter le poids vers la droite.',
        );
        self::assertStringContainsString('? event.deltaX > 0', $journalPage);
    }
}
