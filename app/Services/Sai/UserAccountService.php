<?php

namespace App\Services\Sai;

use App\Models\Sai\User;
use App\Repositories\Sai\UserRepository;

class UserAccountService
{
    public function __construct(private readonly UserRepository $users) {}

    public function delete(User $user): void
    {
        $this->users->softDelete($user);
    }
}
