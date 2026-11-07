<?php

namespace Database\Factories;

use App\Helpers\TurkishSlug;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $name = 'Kahve '.fake()->unique()->words(2, true);

        return [
            'category_id' => Category::factory(),
            'sku' => 'SH-'.strtoupper(fake()->unique()->bothify('###??')),
            'name' => $name,
            'slug' => TurkishSlug::make($name),
            'short_description' => fake()->sentence(),
            'description' => fake()->paragraph(),
            'base_price' => 120.00,
            'min_price' => 90.00,
            'max_price' => 200.00,
            'tags' => ['signature'],
            'temperature_options' => ['hot', 'cold'],
            'is_featured' => true,
            'is_active' => true,
            'sort_order' => 1,
        ];
    }
}
