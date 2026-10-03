<?php

namespace Database\Factories\Sai;

use App\Models\Sai\Country;
use App\Models\Sai\Governorate;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Governorate> */
class GovernorateFactory extends Factory
{
    protected $model = Governorate::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'country_id' => Country::factory(),
            'is_active' => true,
            'ar' => ['name' => 'محافظة '.fake()->unique()->numberBetween(1000, 9999)],
            'en' => ['name' => fake()->unique()->city()],
        ];
    }
}
