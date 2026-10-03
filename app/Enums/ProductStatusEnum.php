<?php

namespace App\Enums;

use App\Enums\Concerns\HasValues;

enum ProductStatusEnum: string
{
    use HasValues;

    case Draft = 'draft';
    case PendingReview = 'pending_review';
    case Published = 'published';
    case Hidden = 'hidden';
    case OutOfStock = 'out_of_stock';
}
