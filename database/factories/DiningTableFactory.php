<?php

namespace Database\Factories;

use App\Models\Branch;
use App\Models\DiningTable;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DiningTable>
 */
class DiningTableFactory extends Factory
{
    protected $model = DiningTable::class;

    public function definition(): array
    {
        $num = (string) fake()->unique()->numberBetween(1, 999);

        return [
            'branch_id' => Branch::factory(),
            'name' => "Masa {$num}",
            'table_number' => $num,
            'token' => DiningTable::generateUniqueToken(),
            'is_active' => true,
            'sort_order' => 1,
        ];
    }
}
