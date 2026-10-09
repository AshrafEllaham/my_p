<?php

namespace App\Repositories\Sai;

use App\Models\Sai\ProductFeature;
use App\Repositories\MainRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ProductFeatureRepository extends MainRepository
{
    public function __construct(ProductFeature $model)
    {
        $this->model = $model;
    }

    public function query(): Builder
    {
        return $this->getModel()->newQuery();
    }

    public function findOrFail(int|string $id): Model
    {
        return $this->query()->findOrFail($id);
    }

    /** @param array<string, mixed> $data */
    public function createRecord(array $data): Model
    {
        $record = $this->getModel()->newInstance();
        $record->fill($data);
        $record->save();

        return $record;
    }

    /** @param array<string, mixed> $data */
    public function updateRecord(int|string $id, array $data): Model
    {
        $record = $this->findOrFail($id);
        $record->fill($data);
        $record->save();

        return $record;
    }

    public function deleteRecord(int|string $id): bool
    {
        return (bool) $this->findOrFail($id)->delete();
    }

    public function deleteForProduct(int|string $productId): int
    {
        return $this->query()->where('product_id', $productId)->delete();
    }

    /** @param array<int, array<string, mixed>> $records */
    public function createManyRecords(array $records): void
    {
        if ($records === []) {
            return;
        }

        $timestamp = now();
        $records = array_map(fn (array $record): array => $record + [
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ], $records);

        $this->query()->insert($records);
    }
}
