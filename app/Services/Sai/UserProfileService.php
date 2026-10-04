<?php

namespace App\Services\Sai;

use App\Enums\AccountTypeEnum;
use App\Repositories\Sai\UserRepository;
use Illuminate\Validation\ValidationException;

class UserProfileService
{
    public function __construct(private readonly UserRepository $users) {}

    /** @param array<string, mixed> $data */
    public function update(int $userId, array $data): object
    {
        $user = $this->users->findOrFail($userId);

        if ($user->account_type !== AccountTypeEnum::User) {
            throw ValidationException::withMessages([
                'account_type' => __('messages.profile.user_type_required'),
            ]);
        }

        return $this->users->updateProfile($userId, [
            'name' => $data['name'],
            'avatar' => $data['image'],
            ...(array_key_exists('address', $data) ? ['address_line' => $data['address']] : []),
        ]);
    }
}
