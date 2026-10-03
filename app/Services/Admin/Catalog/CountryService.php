<?php

namespace App\Services\Admin\Catalog;

use App\Models\Sai\Country;
use App\Repositories\Admin\Catalog\CountryRepository;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class CountryService
{
    public function __construct(
        private readonly CountryRepository $repository,
        private readonly DatabaseManager $database,
    ) {}

    public function dataTableQuery(string $locale): Builder
    {
        return $this->repository->dataTableQuery($locale);
    }

    /** @return Collection<int, Country> */
    public function options(string $locale): Collection
    {
        return $this->repository->options($locale);
    }

    public function find(int $id): Country
    {
        return $this->repository->findForAdmin($id);
    }

    /** @param array<string, mixed> $data */
    public function create(array $data): Country
    {
        return $this->database->transaction(fn (): Country => $this->repository->create($data));
    }

    /** @param array<string, mixed> $data */
    public function update(int $id, array $data): Country
    {
        return $this->database->transaction(
            fn (): Country => $this->repository->updateCountry($id, $data),
        );
    }

    public function delete(int $id): void
    {
        $this->database->transaction(function () use ($id): void {
            $this->repository->findForAdmin($id);

            if ($this->repository->hasGovernorates($id)) {
                throw ValidationException::withMessages([
                    'delete' => __('admin.catalog.errors.country_has_governorates'),
                ]);
            }

            $this->repository->delete($id);
        });
    }
}
