<?php

namespace App\Http\Resources\Concerns;

use Carbon\CarbonInterface;

trait FormatsTimestamps
{
    protected function formatTimestamp(?CarbonInterface $timestamp): ?string
    {
        return $timestamp?->format('Y-m-d h:i A');
    }
}
