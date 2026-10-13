<?php

namespace App\Services\Sai;

use App\Repositories\Sai\ProductRepository;
use Illuminate\Database\Eloquent\Builder;

class UserProductCatalogService
{
    public function __construct(private readonly ProductRepository $products) {}

    /** @param array<string, mixed> $filters */
    public function list(array $filters, string $locale, int $userId): Builder
    {
        return $this->products->listForUserCatalog($filters, $locale, $userId);
    }
}
