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

    /**
     * Overdue: past-due, still active, not returned.
     */
    public function overdue(): static
    {
        return $this->state(fn (array $attributes) => [
            'checked_out_at' => now()->subDays(10),
            'due_at' => now()->subDays(3),
            'returned_at' => null,
            'status' => RentalStatus::Active,
        ]);
    }

    /**
     * Returned: completed rental cycle.
     */
    public function returned(): static
    {
        return $this->state(fn (array $attributes) => [
            'checked_out_at' => now()->subDays(14),
            'due_at' => now()->subDays(7),
            'returned_at' => now()->subDays(5),
            'status' => RentalStatus::Returned,
        ]);
    }
}
