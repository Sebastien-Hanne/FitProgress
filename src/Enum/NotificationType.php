<?php

namespace App\Enum;

enum NotificationType: string {
    case new_coach_request = 'new_coach_request';
    case new_message = 'new_message';
    case session_scheduled = 'session_scheduled';
    case session_modified = 'session_modified';
    case session_cancelled = 'session_cancelled';
    case request_accepted = 'request_accepted';
    case request_rejected = 'request_rejected';
    case certificate_approved = 'certificate_approved';
    case certificate_rejected = 'certificate_rejected';
    case new_feedback = 'new_feedback';
    case weigh_in_reminder = 'weigh_in_reminder';
    case journal_reminder = 'journal_reminder';
}
