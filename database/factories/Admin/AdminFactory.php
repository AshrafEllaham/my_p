<?php

namespace Database\Factories\Admin;

use App\Enums\AdminTypeEnum;
use App\Models\Admin\Admin;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Admin> */
class AdminFactory extends Factory
{
    protected $model = Admin::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => null,
            'password' => 'password',
            'admin_type' => AdminTypeEnum::Admin,
            'is_active' => true,
            'is_blocked' => false,
        ];
    }
}
