<?php

namespace App\Services\Admin\Catalog;

use App\Models\Sai\AdPackage;
use App\Repositories\Admin\Catalog\AdPackageRepository;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;

class AdPackageService
{
    public function __construct(
        private readonly AdPackageRepository $repository,
        private readonly DatabaseManager $database,
    ) {}

    public function dataTableQuery(string $locale): Builder
    {
        return $this->repository->dataTableQuery($locale);
    }

    public function find(int $id): AdPackage
    {
        return $this->repository->findForAdmin($id);
    }

    /** @param array<string, mixed> $data */
    public function create(array $data): AdPackage
    {
        return $this->database->transaction(fn (): AdPackage => $this->repository->create($data));
    }

    /** @param array<string, mixed> $data */
    public function update(int $id, array $data): AdPackage
    {
        return $this->database->transaction(fn (): AdPackage => $this->repository->updatePackage($id, $data));
    }

    public function delete(int $id): void
    {
        $this->database->transaction(function () use ($id): void {
            $this->repository->findForAdmin($id);

            if ($this->repository->hasAds($id)) {
                throw ValidationException::withMessages([
                    'delete' => [__('admin.ad_packages.errors.in_use')],
                ]);
            }

            $this->repository->deletePackage($id);
        });
    }
}
