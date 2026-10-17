<?php

namespace App\Http\Resources\Api\Wallet;

use App\Models\Sai\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin WalletTransaction */
class WalletTransactionResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->public_id,
            'type' => $this->type->value,
            'status' => $this->status->value,
            'amount' => (string) $this->amount,
            'balance_before' => (string) $this->balance_before,
            'balance_after' => (string) $this->balance_after,
            'currency' => $this->currency,
            'completed_at' => $this->completed_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
