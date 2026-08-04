<?php

namespace App\Enum;

enum Mood: string
{
    case VeryBad = 'very_bad';
    case Bad = 'bad';
    case Stable = 'stable';
    case Good = 'good';
    case Excellent = 'excellent';

    public function label(): string
    {
        return match ($this) {
            self::VeryBad => 'Très mauvaise',
            self::Bad => 'Mauvaise',
            self::Stable => 'Stable',
            self::Good => 'Bonne',
            self::Excellent => 'Excellente',
        };
    }
}
