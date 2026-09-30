<?php

namespace App\Services\Admin;

class HomeService
{
    /**
     * @return array{
     *     stats: list<array{label: string, value: int, tone: string}>,
     *     activity: list<array{title: string, description: string, time: string}>
     * }
     */
    public function getDashboardData(): array
    {
        return [
            'stats' => [
                ['label' => __('admin.stats.users'), 'value' => 0, 'tone' => 'success'],
                ['label' => __('admin.stats.orders'), 'value' => 0, 'tone' => 'neutral'],
                ['label' => __('admin.stats.stores'), 'value' => 0, 'tone' => 'warning'],
                ['label' => __('admin.stats.revenue'), 'value' => 0, 'tone' => 'primary'],
            ],
            'activity' => [],
        ];
    }
}
