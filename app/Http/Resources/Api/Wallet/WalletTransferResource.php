<?php

namespace App\Http\Resources\Api\Wallet;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WalletTransferResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        $transfer = $this->resource;

        return [
            'id' => $transfer['id'],
            'recipient' => [
                'name' => $transfer['recipient_name'],
            ],
            'amount' => $transfer['amount'],
            'currency' => $transfer['currency'],
            'status' => $transfer['status']->value,
            'balance_before' => $transfer['balance_before'],
            'balance_after' => $transfer['balance_after'],
            'completed_at' => $transfer['completed_at'],
        ];
    }
}
