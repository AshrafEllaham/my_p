<?php

namespace App\Enums;

use App\Enums\Concerns\HasValues;

enum CouponStatusEnum: string
{
    use HasValues;

    case Active = 'active';
    case Paused = 'paused';
    case Expired = 'expired';
    case Exhausted = 'exhausted';
}
