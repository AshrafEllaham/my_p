<?php

namespace App\Http\Resources\Api\Store;

use App\Models\Sai\Ad;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Ad */
class StoreAdDetailsResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'public_id' => $this->public_id,
            'store_id' => $this->store_id,
            'ad_package_id' => $this->ad_package_id,
            'product_id' => $this->product_id,
            'category_id' => $this->category_id,
            'title' => $this->title,
            'action_label' => $this->action_label,
            'caption' => $this->caption,
            'placement' => $this->placement->value,
            'action' => $this->action->value,
            'media_type' => $this->media_type->value,
            'media_path' => $this->media_path,
            'status' => $this->status->value,
            'cost' => $this->cost,
            'currency' => $this->currency,
            'starts_at' => $this->starts_at?->toISOString(),
            'ends_at' => $this->ends_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
            'package' => $this->whenLoaded('adPackage', fn () => [
                'id' => $this->adPackage->id,
                'name' => $this->adPackage->name,
                'duration_days' => $this->adPackage->duration_days,
            ]),
            'product' => $this->whenLoaded('product', fn () => $this->product === null ? null : [
                'id' => $this->product->id,
                'name' => $this->product->name,
            ]),
            'category' => $this->whenLoaded('category', fn () => $this->category === null ? null : [
                'id' => $this->category->id,
                'name' => $this->category->name,
            ]),
            'metrics' => $this->metrics_summary,
            'daily_metrics' => $this->whenLoaded('dailyMetrics', fn () => $this->dailyMetrics->map(fn ($metric) => [
                'date' => $metric->metric_date->toDateString(),
                'impressions' => $metric->impressions,
                'clicks' => $metric->clicks,
                'chats_started' => $metric->chats_started,
                'orders_attributed' => $metric->orders_attributed,
                'revenue_attributed' => $metric->revenue_attributed,
            ])),
        ];
    }
}
