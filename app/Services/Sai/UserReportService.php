<?php

namespace App\Services\Sai;

use App\Enums\UserReportTypeEnum;
use App\Repositories\Sai\ProductRepository;
use App\Repositories\Sai\ReportReasonRepository;
use App\Repositories\Sai\StoreRepository;
use App\Repositories\Sai\UserReportRepository;
use Illuminate\Support\Collection;

class UserReportService
{
    public function __construct(
        private readonly ProductRepository $products,
        private readonly StoreRepository $stores,
        private readonly ReportReasonRepository $reasons,
        private readonly UserReportRepository $reports,
    ) {}

    public function reasons(string $locale, UserReportTypeEnum $type): Collection
    {
        return $this->reasons->listForUser($locale, $type);
    }

    public function reportProduct(int $productId, int $userId, int $reasonId): int
    {
        $this->products->categoryIdForVisibleProduct($productId);

        return $this->reports->submit($userId, UserReportTypeEnum::Product, $productId, $reasonId);
    }

    public function reportStore(int $storeId, int $userId, int $reasonId): int
    {
        $this->stores->ensureVisibleForUser($storeId);

        return $this->reports->submit($userId, UserReportTypeEnum::Store, $storeId, $reasonId);
    }
}
