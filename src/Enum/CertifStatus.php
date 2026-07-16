<?php

namespace App\Enum;

enum CertifStatus: string {
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';
}