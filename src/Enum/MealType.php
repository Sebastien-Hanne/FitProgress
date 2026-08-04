<?php

namespace App\Enum;

enum MealType: string {
    case Breakfast = 'breakfast';
    case Lunch = 'lunch';
    case Dinner = 'dinner';
    case Snack = 'snack';

    public function label(): string
    {
        return match ($this) {
            self::Breakfast => 'Petit-déjeuner',
            self::Lunch => 'Déjeuner',
            self::Dinner => 'Dîner',
            self::Snack => 'Collation',
        };
    }
}
