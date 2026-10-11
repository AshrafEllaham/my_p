<?php

namespace App\Http\Resources\Api\Store;

use App\Services\Sai\Results\CouponDetailsResult;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin CouponDetailsResult */
class StoreCouponDetailsResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            ...StoreCouponResource::make($this->coupon)->resolve($request),
            'statistics' => $this->statistics,
        ];
    }
}
