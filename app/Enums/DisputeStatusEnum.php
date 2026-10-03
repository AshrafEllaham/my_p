<?php

namespace App\Enums;

use App\Enums\Concerns\HasValues;

enum DisputeStatusEnum: string
{
    use HasValues;

    case Open = 'open';
    case InformationRequested = 'information_requested';
    case UnderReview = 'under_review';
    case OrderCompleted = 'order_completed';
    case Refunded = 'refunded';
    case Rejected = 'rejected';
}
