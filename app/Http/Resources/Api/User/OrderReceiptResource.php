<?php

namespace App\Http\Resources\Api\User;

use App\Models\Sai\OrderReceipt;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin OrderReceipt */
class OrderReceiptResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_id' => $this->order_id,
            'receipt_number' => $this->receipt_number,
            'snapshot' => $this->snapshot,
            'pdf_url' => $this->pdf_path === null ? null : asset('storage/'.$this->pdf_path),
            'issued_at' => $this->issued_at?->toIso8601String(),
        ];
    }
}
