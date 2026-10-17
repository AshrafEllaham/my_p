<?php

namespace App\Http\Resources\Api\Wallet;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WalletWithdrawalRequestResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this['id'],
            'method' => $this['method'],
            'destination' => $this['destination'],
            'amount' => $this['amount'],
            'currency' => $this['currency'],
            'status' => $this['status'],
            'review_note' => $this['review_note'] ?? null,
            'created_at' => $this['created_at'],
        ];
    }
}
