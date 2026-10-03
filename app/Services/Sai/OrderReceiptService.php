<?php

namespace App\Services\Sai;

use App\Repositories\Sai\OrderReceiptRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class OrderReceiptService
{
    public function __construct(private readonly OrderReceiptRepository $repository) {}

    public function query(): Builder
    {
        return $this->repository->query();
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
}
