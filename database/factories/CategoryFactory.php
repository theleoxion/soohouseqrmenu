<?php

namespace Database\Factories;

use App\Helpers\TurkishSlug;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition(): array
    {
        $name = 'Kategori '.fake()->unique()->words(2, true);

        return [
            'name' => $name,
            'slug' => TurkishSlug::make($name),
            'description' => fake()->sentence(),
            'sort_order' => 1,
            'is_active' => true,
        ];
    }
}
