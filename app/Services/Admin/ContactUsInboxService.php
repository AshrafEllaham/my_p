<?php

namespace App\Services\Admin;

use App\Repositories\Admin\ContactUsRepository;
use Illuminate\Database\Eloquent\Builder;

class ContactUsInboxService
{
    public function __construct(private readonly ContactUsRepository $repository) {}

    public function dataTableQuery(): Builder
    {
        return $this->repository->dataTableQuery();
    }

    public function delete(int $id): void
    {
        $this->repository->deleteMessage($id);
    }
}
