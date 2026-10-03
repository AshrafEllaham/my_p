<?php

namespace App\Enums;

use App\Enums\Concerns\HasValues;

enum RequestStatusEnum: string
{
    use HasValues;

    case Pending = 'pending';
    case Processing = 'processing';
    case Completed = 'completed';
    case Failed = 'failed';
    case Rejected = 'rejected';
    case Cancelled = 'cancelled';
}
