<?php

namespace App\Services\Admin\Catalog;

use App\Models\Twenty\Country;
use App\Models\Twenty\Governorate;
use App\Repositories\Admin\Catalog\CountryRepository;
use App\Repositories\Admin\Catalog\GovernorateRepository;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class GovernorateService
{
    public function __construct(
        private readonly GovernorateRepository $repository,
        private readonly CountryRepository $countryRepository,
        private readonly DatabaseManager $database,
    ) {}

    public function dataTableQuery(string $locale): Builder
    {
        return $this->repository->dataTableQuery($locale);
    }

    /** @return Collection<int, Country> */
    public function countryOptions(string $locale): Collection
    {
        return $this->countryRepository->options($locale);
    }

    public function find(int $id): Governorate
    {
        return $this->repository->findForAdmin($id);
    }

    /** @param array<string, mixed> $data */
    public function create(array $data): Governorate
    {
        return $this->database->transaction(fn (): Governorate => $this->repository->create($data));
    }

    /** @param array<string, mixed> $data */
    public function update(int $id, array $data): Governorate
    {
        return $this->database->transaction(
            fn (): Governorate => $this->repository->updateGovernorate($id, $data),
        );
    }

    public function delete(int $id): void
    {
        $this->database->transaction(function () use ($id): void {
            $this->repository->findForAdmin($id);

            if ($this->repository->hasCities($id)) {
                throw ValidationException::withMessages([
                    'delete' => __('admin.catalog.errors.governorate_has_cities'),
                ]);
            }

            $this->repository->delete($id);
        });
    }
}
