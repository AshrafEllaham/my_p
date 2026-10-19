<?php

namespace App\Repositories\Sai;

use App\Models\Sai\ReturnRequestMedia;
use App\Repositories\MainRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ReturnRequestMediaRepository extends MainRepository
{
    public function __construct(ReturnRequestMedia $model)
    {
        $this->model = $model;
    }

    public function query(): Builder
    {
        return $this->getModel()->newQuery();
    }

    /** @param array<int, array{return_request_id: int, path: string, mime_type: string|null, sort_order: int}> $records */
    public function createManyForRequest(array $records): void
    {
        $timestamp = now();
        $rows = array_map(static fn (array $record): array => [
            ...$record,
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ], $records);

        $this->getModel()->newQuery()->insert($rows);
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
}
