<?php

namespace Database\Factories;

use App\Enums\RentalStatus;
use App\Models\Equipment;
use App\Models\Rental;
use App\Models\Staff;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Rental>
 */
class RentalFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'equipment_id' => Equipment::factory(),
            'staff_id' => Staff::factory(),
            'checked_out_at' => now(),
            'due_at' => now()->addDays(7),
            'returned_at' => null,
            'status' => RentalStatus::Active,
        ];
    }
}
