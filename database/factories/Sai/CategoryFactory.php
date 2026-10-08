<?php

namespace Database\Factories\Sai;

use App\Models\Sai\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<Category> */
class CategoryFactory extends Factory
{
    protected $model = Category::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'parent_id' => null,
            'image' => null,
            'is_active' => true,
            'sort_order' => fake()->numberBetween(0, 100),
            'ar' => ['name' => 'قسم '.fake()->unique()->numberBetween(1000, 9999), 'description' => null],
            'en' => ['name' => Str::title($name), 'description' => null],
        ];
    }

    public function subcategory(Category $parent): static
    {
        return $this->state(fn (): array => ['parent_id' => $parent->getKey()]);
    }
}
