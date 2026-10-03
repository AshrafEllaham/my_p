<?php

namespace App\Enums;

use App\Enums\Concerns\HasValues;

enum CouponDiscountTypeEnum: string
{
    use HasValues;

    case Percentage = 'percentage';
    case Fixed = 'fixed';
}
