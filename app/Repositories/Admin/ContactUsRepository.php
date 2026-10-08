<?php

namespace App\Repositories\Admin;

use App\Models\Sai\ContactUs;
use App\Repositories\MainRepository;
use Illuminate\Database\Eloquent\Builder;

class ContactUsRepository extends MainRepository
{
    public function __construct(ContactUs $model)
    {
        $this->model = $model;
    }

    public function dataTableQuery(): Builder
    {
        return $this->getModel()->newQuery()
            ->select(['id', 'name', 'email', 'subject', 'message'])
            ->latest('id');
    }

    public function deleteMessage(int $id): void
    {
        $this->getModel()->newQuery()->findOrFail($id)->delete();
    }
}
