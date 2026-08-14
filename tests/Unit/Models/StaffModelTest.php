<?php

namespace Tests\Unit\Models;

use App\Enums\RentalStatus;
use App\Models\Rental;
use App\Models\Staff;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StaffModelTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test Staff has many rentals.
     */
    public function test_staff_has_many_rentals(): void
    {
        $staff = Staff::factory()->create();
        Rental::factory()->for($staff)->count(3)->create();

        $this->assertCount(3, $staff->rentals);
        $staff->rentals->each(function ($rental) use ($staff) {
            $this->assertEquals($staff->id, $rental->staff_id);
        });
    }

    /**
     * Test Staff activeRentals returns only active rentals.
     */
    public function test_staff_active_rentals_returns_only_active_status(): void
    {
        $staff = Staff::factory()->create();

        Rental::factory()->for($staff)->create(['status' => RentalStatus::Active]);
        Rental::factory()->for($staff)->create(['status' => RentalStatus::Active]);
        Rental::factory()->for($staff)->create(['status' => RentalStatus::Returned]);

        $activeRentals = $staff->activeRentals;

        $this->assertCount(2, $activeRentals);
        $activeRentals->each(function ($rental) {
            $this->assertEquals(RentalStatus::Active, $rental->status);
        });
    }

    /**
     * Test Staff belongs to User.
     */
    public function test_staff_belongs_to_user(): void
    {
        $user = User::factory()->create();
        $staff = Staff::factory()->for($user)->create();

        $this->assertEquals($user->id, $staff->user->id);
    }

    /**
     * Test Staff can have null user_id.
     */
    public function test_staff_can_have_null_user(): void
    {
        $staff = Staff::factory()->create(['user_id' => null]);

        $this->assertNull($staff->user_id);
        $this->assertNull($staff->user);
    }
}
