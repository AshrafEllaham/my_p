<?php

namespace App\Http\Resources\Api\Store;

use App\Enums\CouponStatusEnum;
use App\Http\Resources\Concerns\FormatsTimestamps;
use App\Models\Sai\Coupon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Coupon */
class StoreCouponResource extends JsonResource
{
    use FormatsTimestamps;

    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'store_id' => $this->store_id,
            'code' => $this->code,
            'discount_type' => $this->discount_type->value,
            'discount_value' => $this->discount_value,
            'minimum_order_amount' => $this->minimum_order_amount,
            'maximum_discount_amount' => $this->maximum_discount_amount,
            'max_uses' => $this->max_uses,
            'max_uses_per_user' => $this->max_uses_per_user,
            'uses_count' => $this->uses_count,
            'status' => $this->status->value,
            'is_active' => $this->status === CouponStatusEnum::Active,
            'starts_at' => $this->formatTimestamp($this->starts_at),
            'expires_at' => $this->formatTimestamp($this->expires_at),
            'created_at' => $this->formatTimestamp($this->created_at),
        ];
    }
}
