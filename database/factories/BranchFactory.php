<?php

namespace Database\Factories;

use App\Helpers\TurkishSlug;
use App\Models\Branch;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Branch>
 */
class BranchFactory extends Factory
{
    protected $model = Branch::class;

    public function definition(): array
    {
        $name = 'Soohouse '.fake()->unique()->city();

        return [
            'name' => $name,
            'slug' => TurkishSlug::make($name),
            'city' => 'İstanbul',
            'district' => 'Kadıköy',
            'address' => fake()->address(),
            'latitude' => 40.9819,
            'longitude' => 29.0268,
            'phone' => '+90 216 000 00 00',
            'whatsapp' => '905000000000',
            'email' => fake()->unique()->safeEmail(),
            'is_active' => true,
            'sort_order' => 1,
        ];
    }
}
