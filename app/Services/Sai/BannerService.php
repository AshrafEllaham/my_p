<?php

namespace App\Services\Sai;

use App\Enums\AccountTypeEnum;
use App\Models\Sai\Banner;
use App\Repositories\Sai\BannerRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class BannerService
{
    public function __construct(private readonly BannerRepository $repository) {}

    public function query(): Builder
    {
        return $this->repository->query();
    }

    public function queryForType(AccountTypeEnum $type): Builder
    {
        return $this->repository->queryForType($type);
    }

    /** @return Collection<int, Banner> */
    public function list(?AccountTypeEnum $type): Collection
    {
        return $this->repository->getForType($type);
    }

    public function find(int|string $id): Model
    {
        return $this->repository->findOrFail($id);
    }

    /** @param array{file: string, type: AccountTypeEnum|string} $data */
    public function create(array $data): Model
    {
        return $this->repository->createRecord($data);
    }

    /** @param array{file?: string, type?: AccountTypeEnum|string} $data */
    public function update(int|string $id, array $data): Model
    {
        return $this->repository->updateRecord($id, $data);
    }

    public function delete(int|string $id): bool
    {
        return $this->repository->deleteRecord($id);
    }
}
