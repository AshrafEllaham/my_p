<?php

namespace App\Http\Resources\Api\Store;

use App\Http\Resources\Concerns\FormatsTimestamps;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StoreProductDetailsResource extends JsonResource
{
    use FormatsTimestamps;

    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        $analytics = $this['analytics'];
        $latestReview = $analytics['latest_review'];

        return [
            'product' => StoreProductResource::make($this['product'])->resolve($request),
            'analytics' => [
                'sales' => [
                    'total_revenue' => $analytics['total_revenue'],
                    'completed_orders_count' => $analytics['completed_orders_count'],
                    'units_sold' => $analytics['units_sold'],
                    'revenue_last_30_days' => $analytics['revenue_last_30_days'],
                    'units_sold_last_30_days' => $analytics['units_sold_last_30_days'],
                    'daily_sales' => $analytics['daily_sales'],
                ],
                'ads' => $analytics['ad_metrics'],
                'reviews' => [
                    'average_rating' => $analytics['average_rating'],
                    'total_reviews' => $analytics['total_reviews'],
                    'rating_distribution' => $analytics['rating_distribution'],
                    'latest' => $latestReview ? [
                        'user_name' => $latestReview->relationLoaded('user') ? $latestReview->user?->name : null,
                        'rating' => $latestReview->rating,
                        'comment' => $latestReview->comment,
                        'created_at' => $this->formatTimestamp($latestReview->created_at),
                    ] : null,
                ],
            ],
        ];
    }
}
