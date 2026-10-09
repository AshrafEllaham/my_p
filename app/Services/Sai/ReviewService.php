<?php

namespace App\Services\Sai;

use App\Repositories\Sai\ReviewRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;

class ReviewService
{
    public function __construct(private readonly ReviewRepository $repository) {}

    public function query(): Builder
    {
        return $this->repository->query();
    }

    /** @param array<string, mixed> $filters */
    public function listForProduct(int $storeId, int $productId, array $filters): Builder
    {
        $this->assertStoreOwner($storeId);
        $this->assertProductBelongsToStore($productId, $storeId);

        return $this->repository->listForProduct($storeId, $productId, $filters);
    }

    /** @return array<string, mixed> */
    public function summaryForProduct(int $storeId, int $productId): array
    {
        $this->assertStoreOwner($storeId);
        $this->assertProductBelongsToStore($productId, $storeId);

        return $this->repository->summaryForProduct($storeId, $productId);
    }

    public function find(int|string $id): Model
    {
        return $this->repository->findOrFail($id);
    }

    /** @param array<string, mixed> $data */
    public function create(array $data): Model
    {
        return $this->repository->createRecord($data);
    }

    /** @param array<string, mixed> $data */
    public function update(int|string $id, array $data): Model
    {
        return $this->repository->updateRecord($id, $data);
    }

    public function delete(int|string $id): bool
    {
        return $this->repository->deleteRecord($id);
    }

    private function assertStoreOwner(int $storeId): void
    {
        if (! $this->repository->isStoreOwner($storeId)) {
            throw ValidationException::withMessages([
                'account_type' => [__('messages.profile.store_type_required')],
            ]);
        }
    }

    private function assertProductBelongsToStore(int $productId, int $storeId): void
    {
        if (! $this->repository->productBelongsToStore($productId, $storeId)) {
            throw (new ModelNotFoundException)->setModel('App\\Models\\Sai\\Product', [$productId]);
        }
    }
}
