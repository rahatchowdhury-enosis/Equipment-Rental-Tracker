<?php

namespace Database\Seeders;

use App\Enums\EquipmentStatus;
use App\Models\Equipment;
use App\Models\Rental;
use App\Models\Staff;
use Illuminate\Database\Seeder;

class RentalSeeder extends Seeder
{
    /**
     * Seed 8 rentals: mostly active, some returned, at least one
     * overdue (feeds the overdue report — TASK-16 — with real data).
     * Each rental's equipment status is kept in sync (CheckedOut while
     * active/overdue, Available once returned) so the demo data never
     * shows an item as both available and checked out.
     */
    public function run(): void
    {
        if (Rental::count() > 0) {
            return;
        }

        $states = ['overdue', null, null, null, 'returned', 'returned', 'returned', null];

        $equipment = Equipment::orderBy('id')->take(count($states))->get()->values();
        $staff = Staff::orderBy('id')->get();

        if ($equipment->count() < count($states) || $staff->isEmpty()) {
            $this->command?->warn(sprintf(
                'RentalSeeder skipped: need at least %d equipment and 1 staff row seeded first.',
                count($states)
            ));

            return;
        }

        foreach ($states as $index => $state) {
            $factory = $state ? Rental::factory()->{$state}() : Rental::factory();
            $item = $equipment[$index];

            $factory->create([
                'equipment_id' => $item->id,
                'staff_id' => $staff[$index % $staff->count()]->id,
            ]);

            $item->update([
                'status' => $state === 'returned' ? EquipmentStatus::Available : EquipmentStatus::CheckedOut,
            ]);
        }
    }
}
