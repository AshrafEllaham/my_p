<?php

namespace Database\Seeders;

use App\Enums\AdminTypeEnum;
use App\Models\Admin\Admin;
use Illuminate\Database\Seeder;
use RuntimeException;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = config('admin.seed.accounts', []);

        if (! is_array($accounts)) {
            throw new RuntimeException('Admin seed accounts configuration must be an array.');
        }

        foreach ($accounts as $key => $account) {
            if (! is_array($account) || ! $this->hasRequiredCredentials($account)) {
                if (app()->environment('production')) {
                    throw new RuntimeException("Complete credentials are required for the [{$key}] admin seed account.");
                }

                continue;
            }

            Admin::query()->firstOrCreate(
                ['email' => $account['email']],
                [
                    'name' => $account['name'],
                    'phone' => $account['phone'] ?? null,
                    'password' => $account['password'],
                    'admin_type' => $account['type'],
                    'is_active' => true,
                    'is_blocked' => false,
                    'main_user_id' => null,
                ],
            );
        }
    }

    /** @param array<string, mixed> $account */
    private function hasRequiredCredentials(array $account): bool
    {
        return is_string($account['name'] ?? null)
            && $account['name'] !== ''
            && is_string($account['email'] ?? null)
            && $account['email'] !== ''
            && is_string($account['password'] ?? null)
            && $account['password'] !== ''
            && $account['type'] instanceof AdminTypeEnum;
    }
}
