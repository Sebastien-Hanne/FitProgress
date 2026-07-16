<?php

namespace App\Enum;

enum Mood: string
{
    case VeryBad = 'very_bad';
    case Bad = 'bad';
    case Stable = 'stable';
    case Good = 'good';
    case Excellent = 'excellent';
}