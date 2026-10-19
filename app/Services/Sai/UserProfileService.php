<?php

namespace App\Services\Sai;

use App\Enums\AccountTypeEnum;
use App\Repositories\Sai\UserRepository;
use Illuminate\Validation\ValidationException;

class UserProfileService
{
    public function __construct(private readonly UserRepository $users) {}

    public function show(int $userId): object
    {
        $user = $this->users->profileForUser($userId);

        if ($user->account_type !== AccountTypeEnum::User) {
            throw ValidationException::withMessages([
                'account_type' => __('messages.profile.user_type_required'),
            ]);
        }

        return $user;
    }

    /** @param array<string, mixed> $data */
    public function update(int $userId, array $data): object
    {
        $user = $this->users->findOrFail($userId);

        if ($user->account_type !== AccountTypeEnum::User) {
            throw ValidationException::withMessages([
                'account_type' => __('messages.profile.user_type_required'),
            ]);
        }

        $updates = ['name' => $data['name']];
        if (array_key_exists('image', $data)) {
            $updates['avatar'] = $data['image'];
        }
        foreach ([
            'address' => 'address_line',
            'country_id' => 'country_id',
            'governorate_id' => 'governorate_id',
            'city_id' => 'city_id',
            'pickup_latitude' => 'pickup_latitude',
            'pickup_longitude' => 'pickup_longitude',
        ] as $input => $column) {
            if (array_key_exists($input, $data)) {
                $updates[$column] = $data[$input];
            }
        }

        return $this->users->updateProfile($userId, $updates);
    }
}
