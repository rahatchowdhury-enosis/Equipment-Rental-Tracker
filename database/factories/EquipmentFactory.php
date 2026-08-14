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
            'name' => fake()->words(2, true),
            'category' => fake()->word(),
            'serial_no' => fake()->unique()->regexify('[A-Z]{3}-[0-9]{6}'),
            'photo' => null,
            'condition' => Condition::Good,
            'status' => EquipmentStatus::Available,
        ];
    }
}
