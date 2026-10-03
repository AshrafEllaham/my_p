<?php

namespace Database\Factories\Sai;

use App\Models\Sai\City;
use App\Models\Sai\Governorate;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<City> */
class CityFactory extends Factory
{
    protected $model = City::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'governorate_id' => Governorate::factory(),
            'is_active' => true,
            'ar' => ['name' => 'مدينة '.fake()->unique()->numberBetween(1000, 9999)],
            'en' => ['name' => fake()->unique()->city()],
        ];
    }
}
