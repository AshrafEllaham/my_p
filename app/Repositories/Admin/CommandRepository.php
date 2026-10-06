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

    /** @param list<string> $allowedCommands
     * @return Collection<int, Command>
     */
    public function listAllowed(array $allowedCommands): Collection
    {
        return $this->model->query()
            ->whereIn('command', $allowedCommands)
            ->orderBy('id')
            ->get(['id', 'command']);
    }
}
