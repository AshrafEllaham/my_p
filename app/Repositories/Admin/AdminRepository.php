<?php

namespace App\Repositories\Admin;

use App\Models\Admin\Admin;
use App\Repositories\MainRepository;

class AdminRepository extends MainRepository
{
    public function __construct(Admin $admin)
    {
        $this->model = $admin;
    }

    /** @param array<string, mixed> $data */
    public function updateProfile(int $adminId, array $data): Admin
    {
        /** @var Admin $admin */
        $admin = $this->find($adminId);
        $admin->fill($data);
        $admin->save();

        return $admin->refresh();
    }
}
