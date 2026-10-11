<?php

namespace App\Services\Sai\Results;

use Illuminate\Database\Eloquent\Builder;

final readonly class CouponListResult
{
    /** @param array{active_coupons_count: int, total_uses_count: int} $summary */
    public function __construct(
        public Builder $query,
        public array $summary,
    ) {}
}
