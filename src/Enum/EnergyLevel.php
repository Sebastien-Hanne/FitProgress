<?php

namespace App\Enum;

enum EnergyLevel: int
{
    case VeryLow = 1;
    case Low = 2;
    case Medium = 3;
    case High = 4;
    case VeryHigh = 5;

    public function label(): string
    {
        return match ($this) {
            self::VeryLow => 'Très faible',
            self::Low => 'Faible',
            self::Medium => 'Moyenne',
            self::High => 'Élevée',
            self::VeryHigh => 'Très élevée',
        };
    }
}