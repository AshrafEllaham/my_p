<?php

namespace App\Services\Sai;

use App\Repositories\Sai\UserRepository;

class AccountTypeService
{
    public function __construct(private readonly UserRepository $users) {}

    /** @param array<string, mixed> $data */
    public function update(int $accountId, array $data): object
    {
        if (array_key_exists('country_id', $data) && $data['country_id'] === null) {
            $data['governorate_id'] = null;
            $data['city_id'] = null;
        } elseif (array_key_exists('governorate_id', $data) && $data['governorate_id'] === null) {
            $data['city_id'] = null;
        } elseif (array_key_exists('country_id', $data) && ! array_key_exists('governorate_id', $data)) {
            $data['governorate_id'] = null;
            $data['city_id'] = null;
        } elseif (array_key_exists('governorate_id', $data) && ! array_key_exists('city_id', $data)) {
            $data['city_id'] = null;
        }

        return $this->users->updateRecord($accountId, $data);
    }
}
