<?php

namespace App\Enums;

use App\Enums\Concerns\HasValues;

enum SettlementStatusEnum: string
{
    use HasValues;

    case Pending = 'pending';
    case Processing = 'processing';
    case Completed = 'completed';
    case ReviewRequired = 'review_required';
    case Rejected = 'rejected';
}
