<?php

namespace App\Services\Sai;

use App\Enums\AccountTypeEnum;
use App\Repositories\Sai\StoreRepository;
use App\Repositories\Sai\UserRepository;
use Illuminate\Database\DatabaseManager;
use Illuminate\Validation\ValidationException;

class StoreProfileService
{
    public function __construct(
        private readonly UserRepository $users,
        private readonly StoreRepository $stores,
        private readonly DatabaseManager $database,
    ) {}

    /** @param array<string, mixed> $data */
    public function update(int $ownerId, array $data): object
    {
        $owner = $this->users->findOrFail($ownerId);

        if ($owner->account_type !== AccountTypeEnum::Store) {
            throw ValidationException::withMessages([
                'account_type' => __('messages.profile.store_type_required'),
            ]);
        }

        return $this->database->transaction(function () use ($ownerId, $data): object {
            $this->users->updateProfile($ownerId, [
                'name' => $data['name'],
                'avatar' => $data['image'],
                ...(array_key_exists('address', $data) ? ['address_line' => $data['address']] : []),
            ]);

            return $this->stores->updateOrCreateForOwner($ownerId, [
                'category_id' => $data['category_id'],
                ...(array_key_exists('description', $data) ? ['description' => $data['description']] : []),
                ...(array_key_exists('cover', $data) ? ['cover_image' => $data['cover']] : []),
            ]);
        });
    }
}
