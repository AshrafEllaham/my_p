<?php

namespace App\Repositories\Admin;

use App\Models\Admin\Command;
use App\Repositories\MainRepository;
use Illuminate\Database\Eloquent\Collection;

class CommandRepository extends MainRepository
{
    public function __construct(Command $model)
    {
        $this->model = $model;
    }

    /** @return Collection<int, Command> */
    public function listAll(): Collection
    {
        return $this->model->query()
            ->orderBy('id')
            ->get(['id', 'command']);
    }

    public function updateCommand(int $id, string $command): void
    {
        $this->model->query()->findOrFail($id)->update(['command' => $command]);
    }

    public function deleteCommand(int $id): void
    {
        $this->model->query()->findOrFail($id)->delete();
    }
}
