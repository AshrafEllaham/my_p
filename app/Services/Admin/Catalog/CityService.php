<?php

namespace App\Services\Admin\Catalog;

use App\Models\Sai\City;
use App\Models\Sai\Governorate;
use App\Repositories\Admin\Catalog\CityRepository;
use App\Repositories\Admin\Catalog\GovernorateRepository;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class CityService
{
    public function __construct(
        private readonly CityRepository $repository,
        private readonly GovernorateRepository $governorateRepository,
        private readonly DatabaseManager $database,
    ) {}

    public function dataTableQuery(string $locale): Builder
    {
        return $this->repository->dataTableQuery($locale);
    }

    /** @return Collection<int, Governorate> */
    public function governorateOptions(string $locale): Collection
    {
        return $this->governorateRepository->options($locale);
    }

    public function find(int $id): City
    {
        return $this->repository->findForAdmin($id);
    }

    /** @param array<string, mixed> $data */
    public function create(array $data): City
    {
        return $this->database->transaction(fn (): City => $this->repository->create($data));
    }

    /** @param array<string, mixed> $data */
    public function update(int $id, array $data): City
    {
        return $this->database->transaction(fn (): City => $this->repository->updateCity($id, $data));
    }

    public function delete(int $id): void
    {
        $this->database->transaction(function () use ($id): void {
            $this->repository->findForAdmin($id);
            $this->repository->delete($id);
        });
    }
}
