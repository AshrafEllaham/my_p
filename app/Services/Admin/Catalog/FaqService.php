<?php

namespace App\Services\Admin\Catalog;

use App\Enums\AccountTypeEnum;
use App\Models\Sai\Faq;
use App\Repositories\Admin\Catalog\FaqRepository;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Eloquent\Builder;

class FaqService
{
    public function __construct(
        private readonly FaqRepository $repository,
        private readonly DatabaseManager $database,
    ) {}

    public function dataTableQuery(string $locale, ?AccountTypeEnum $type): Builder
    {
        return $this->repository->dataTableQuery($locale, $type);
    }

    public function find(int $id): Faq
    {
        return $this->repository->findForAdmin($id);
    }

    /** @param array<string, mixed> $data */
    public function create(array $data): Faq
    {
        return $this->database->transaction(fn (): Faq => $this->repository->create($data));
    }

    /** @param array<string, mixed> $data */
    public function update(int $id, array $data): Faq
    {
        return $this->database->transaction(fn (): Faq => $this->repository->updateFaq($id, $data));
    }

    public function delete(int $id): void
    {
        $this->database->transaction(function () use ($id): void {
            $this->repository->findForAdmin($id);
            $this->repository->deleteFaq($id);
        });
    }
}
