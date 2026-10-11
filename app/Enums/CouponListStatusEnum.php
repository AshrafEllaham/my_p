<?php

namespace App\Enums;

use App\Enums\Concerns\HasValues;

enum CouponListStatusEnum: string
{
    use HasValues;

    case Active = 'active';
    case Inactive = 'inactive';
    case Expired = 'expired';
}
