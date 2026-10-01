<?php

namespace Database\Factories\Twenty;

use App\Models\Twenty\Country;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<Country> */
class CountryFactory extends Factory
{
    protected $model = Country::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        $name = fake()->unique()->country();

        return [
            'code' => strtoupper(Str::random(3)),
            'phone_code' => '+'.fake()->unique()->numberBetween(100, 999),
            'flag' => '🌍',
            'is_active' => true,
            'ar' => ['name' => 'دولة '.fake()->unique()->numberBetween(1000, 9999)],
            'en' => ['name' => $name],
        ];
    }
}
