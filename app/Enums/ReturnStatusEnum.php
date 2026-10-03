<?php

namespace App\Enums;

use App\Enums\Concerns\HasValues;

enum ReturnStatusEnum: string
{
    use HasValues;

    case PendingReview = 'pending_review';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case ProductReceived = 'product_received';
    case Refunded = 'refunded';
    case Exchanged = 'exchanged';
    case Cancelled = 'cancelled';
}
