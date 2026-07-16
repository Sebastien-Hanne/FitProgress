<?php

namespace App\Enum;

enum SessionStatus: string {
    case Scheduled = 'scheduled';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
}