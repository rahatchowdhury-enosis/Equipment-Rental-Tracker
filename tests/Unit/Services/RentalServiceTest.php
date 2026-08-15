<?php

namespace Tests\Unit\Services;

use App\Enums\Condition;
use App\Enums\EquipmentStatus;
use App\Enums\RentalStatus;
use App\Exceptions\EquipmentNotAvailableException;
use App\Models\Equipment;
use App\Models\Rental;
use App\Models\Staff;
use App\Services\RentalService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RentalServiceTest extends TestCase
{
    use RefreshDatabase;

    private RentalService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new RentalService;
    }

    public function test_checkout_throws_when_equipment_not_available(): void
    {
        $equipment = Equipment::factory()->create(['status' => EquipmentStatus::CheckedOut]);
        $staff = Staff::factory()->create();

        $this->expectException(EquipmentNotAvailableException::class);

        $this->service->checkout($equipment, $staff);
    }

    public function test_checkout_throws_when_staff_has_3_active_rentals(): void
    {
        $staff = Staff::factory()->create();
        Rental::factory()->for($staff)->count(3)->create(['status' => RentalStatus::Active]);
        $equipment = Equipment::factory()->create();

        $this->expectException(\DomainException::class);

        $this->service->checkout($equipment, $staff);
    }

    public function test_checkout_succeeds_on_3rd_rental_but_not_4th(): void
    {
        $staff = Staff::factory()->create();
        Rental::factory()->for($staff)->count(2)->create(['status' => RentalStatus::Active]);
        $thirdEquipment = Equipment::factory()->create();

        $rental = $this->service->checkout($thirdEquipment, $staff);

        $this->assertSame(RentalStatus::Active, $rental->status);

        $fourthEquipment = Equipment::factory()->create();

        $this->expectException(\DomainException::class);

        $this->service->checkout($fourthEquipment, $staff);
    }

    public function test_checkout_marks_equipment_checked_out_and_sets_due_date(): void
    {
        $equipment = Equipment::factory()->create();
        $staff = Staff::factory()->create();

        $rental = $this->service->checkout($equipment, $staff);

        $this->assertSame(EquipmentStatus::CheckedOut, $equipment->fresh()->status);
        $this->assertTrue($rental->due_at->isSameDay(now()->addDays(7)));
    }

    public function test_return_rental_transitions_equipment_and_sets_condition(): void
    {
        $equipment = Equipment::factory()->create(['status' => EquipmentStatus::CheckedOut]);
        $rental = Rental::factory()->for($equipment)->create(['status' => RentalStatus::Active]);

        $returned = $this->service->returnRental($rental, Condition::Damaged);

        $this->assertSame(RentalStatus::Returned, $returned->status);
        $this->assertSame(EquipmentStatus::Available, $equipment->fresh()->status);
        $this->assertSame(Condition::Damaged, $equipment->fresh()->condition);
    }

    public function test_return_rental_throws_when_already_returned(): void
    {
        $rental = Rental::factory()->returned()->create();

        $this->expectException(\DomainException::class);

        $this->service->returnRental($rental, Condition::Good);
    }

    public function test_calculate_late_fee_is_zero_when_not_late(): void
    {
        $rental = Rental::factory()->create([
            'due_at' => now()->addDays(7),
            'returned_at' => null,
        ]);

        $this->assertSame(0, $this->service->calculateLateFee($rental));
    }

    public function test_calculate_late_fee_is_1000_cents_for_2_days_late(): void
    {
        $rental = Rental::factory()->create([
            'due_at' => now()->subDays(2),
            'returned_at' => now(),
        ]);

        $this->assertSame(1000, $this->service->calculateLateFee($rental));
    }

    public function test_calculate_late_fee_never_negative_when_returned_early(): void
    {
        $rental = Rental::factory()->create([
            'due_at' => now()->addDays(3),
            'returned_at' => now(),
        ]);

        $this->assertSame(0, $this->service->calculateLateFee($rental));
    }
}
