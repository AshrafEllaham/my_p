<?php

namespace App\Services\Sai\Results;

use Illuminate\Database\Eloquent\Model;

final readonly class CouponDetailsResult
{
    /** @param array<string, mixed> $statistics */
    public function __construct(public Model $coupon, public array $statistics) {}
}
