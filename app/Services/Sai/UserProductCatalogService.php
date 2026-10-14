<?php

namespace App\Services\Sai;

use App\Repositories\Sai\ProductRepository;
use App\Repositories\Sai\ReviewRepository;
use App\Repositories\Sai\StorePolicyVersionRepository;
use Illuminate\Database\Eloquent\Builder;

class UserProductCatalogService
{
    public function __construct(
        private readonly ProductRepository $products,
        private readonly StorePolicyVersionRepository $policies,
        private readonly ReviewRepository $reviews,
    ) {}

    /** @param array<string, mixed> $filters */
    public function list(array $filters, string $locale, int $userId): Builder
    {
        return $this->products->listForUserCatalog($filters, $locale, $userId);
    }

    /** @return array<string, mixed> */
    public function details(int $productId, string $locale, int $userId): array
    {
        $product = $this->products->findForUserDetails($productId, $locale, $userId);

        return [
            'product' => $product,
            'policy' => $this->policies->currentForStore((int) $product->store_id),
            'latest_review' => $this->reviews->latestVisibleForProduct($productId),
            'similar_products' => $this->products->similarForUserDetails($productId, (int) $product->category_id, $locale, $userId),
        ];
    }

    /** @param array<string, mixed> $filters */
    public function similar(int $productId, array $filters, string $locale, int $userId): Builder
    {
        $categoryId = $this->products->categoryIdForVisibleProduct($productId);

        return $this->products->listSimilarForUser($productId, $categoryId, $filters, $locale, $userId);
    }

    /** @param array<string, mixed> $filters */
    public function reviews(int $productId, array $filters): Builder
    {
        $this->products->categoryIdForVisibleProduct($productId);

        return $this->reviews->listVisibleForUserProduct($productId, $filters);
    }

    /** @return array{average_rating: float|null, reviews_count: int} */
    public function reviewSummary(int $productId): array
    {
        return $this->reviews->summaryVisibleForUserProduct($productId);
    }
}
