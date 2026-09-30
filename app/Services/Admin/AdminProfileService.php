<?php

namespace App\Services\Admin;

use App\Models\Admin\Admin;
use App\Repositories\Admin\AdminRepository;

class AdminProfileService
{
    public function __construct(
        private readonly AdminRepository $repository,
    ) {}

    /** @param array<string, mixed> $data */
    public function update(Admin $admin, array $data): Admin
    {
        unset($data['current_password'], $data['password_confirmation']);

        if (empty($data['password'])) {
            unset($data['password']);
        }

        return $this->repository->updateProfile($admin->getKey(), $data);
    }
}
