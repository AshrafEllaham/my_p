<?php

namespace App\Services\Sai;

use App\Models\Sai\User;
use App\Repositories\Sai\UserRepository;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class ChangePasswordService
{
    public function __construct(private readonly UserRepository $users) {}

    /** @param array{current_password: string, password: string, password_confirmation: string} $data */
    public function change(User $user, array $data): void
    {
        if ($user->password === null || ! Hash::check($data['current_password'], $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => __('messages.auth.current_password_invalid'),
            ]);
        }

        $this->users->updatePassword($user, $data['password']);
    }
}
