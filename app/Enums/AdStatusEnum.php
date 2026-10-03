<?php

namespace App\Enums;

use App\Enums\Concerns\HasValues;

enum AdStatusEnum: string
{
    use HasValues;

    case Draft = 'draft';
    case PendingReview = 'pending_review';
    case Scheduled = 'scheduled';
    case Active = 'active';
    case Paused = 'paused';
    case Completed = 'completed';
    case Rejected = 'rejected';
}
