<?php

namespace Tests\Feature;

use App\Enums\EquipmentStatus;
use App\Enums\RentalStatus;
use App\Enums\Role;
use App\Models\Equipment;
use App\Models\Rental;
use App\Models\Staff;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_populates_expected_row_counts(): void
    {
        $this->seed();

        $this->assertSame(10, Equipment::count());
        $this->assertSame(5, Staff::count());
        $this->assertSame(8, Rental::count());
        $this->assertSame(1, Staff::where('role', Role::Admin)->count());
    }

    public function test_seeder_produces_at_least_one_overdue_rental(): void
    {
        $this->seed();

        $overdue = Rental::where('status', RentalStatus::Active)
            ->where('due_at', '<', Carbon::now())
            ->whereNull('returned_at')
            ->exists();

        $this->assertTrue($overdue);
    }

    public function test_seeder_marks_returned_rentals_consistently(): void
    {
        $this->seed();

        $this->assertSame(
            3,
            Rental::where('status', RentalStatus::Returned)->whereNotNull('returned_at')->count()
        );
    }

    public function test_seeder_keeps_equipment_status_in_sync_with_active_rentals(): void
    {
        $this->seed();

        $this->assertSame(
            Rental::where('status', RentalStatus::Active)->count(),
            Equipment::where('status', EquipmentStatus::CheckedOut)->count()
        );

        $this->assertFalse(
            Rental::where('status', RentalStatus::Active)
                ->whereHas('equipment', fn ($query) => $query->where('status', EquipmentStatus::Available))
                ->exists(),
            'An active rental should never point at equipment still marked Available.'
        );

        $this->assertFalse(
            Rental::where('status', RentalStatus::Returned)
                ->whereHas('equipment', fn ($query) => $query->where('status', EquipmentStatus::CheckedOut))
                ->exists(),
            'A returned rental should never point at equipment still marked CheckedOut.'
        );
    }

    public function test_seeder_is_safe_to_run_twice(): void
    {
        $this->seed();
        $this->seed();

        $this->assertSame(10, Equipment::count());
        $this->assertSame(5, Staff::count());
        $this->assertSame(8, Rental::count());
    }
}
