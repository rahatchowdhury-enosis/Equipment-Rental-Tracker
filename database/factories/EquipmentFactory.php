<?php

namespace Database\Factories;

use App\Enums\Condition;
use App\Enums\EquipmentStatus;
use App\Models\Equipment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Equipment>
 */
class EquipmentFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'name' => fake()->randomElement(['Canon EOS R5', 'DeWalt Drill', 'Shure SM7B', 'Pioneer DJ Controller']),
            'category' => fake()->randomElement(['camera', 'tool', 'av']),
            'serial_no' => strtoupper(fake()->unique()->bothify('??-####')),
            'photo' => null,
            'condition' => Condition::Good,
            'status' => EquipmentStatus::Available,
        ];
    }
}
