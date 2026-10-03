<?php

namespace App\Enums;

use App\Enums\Concerns\HasValues;

enum PaymentStatusEnum: string
{
    use HasValues;

    case Pending = 'pending';
    case Paid = 'paid';
    case Failed = 'failed';
    case Refunded = 'refunded';
    case PartiallyRefunded = 'partially_refunded';
}
