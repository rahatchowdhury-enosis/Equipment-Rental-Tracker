<?php

namespace Tests\Feature;

use App\Enums\Condition;
use App\Enums\EquipmentStatus;
use App\Enums\RentalStatus;
use App\Models\Equipment;
use App\Models\Rental;
use App\Models\Staff;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RentalControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_checkout_flow_creates_rental_and_flips_equipment_status(): void
    {
        $user = User::factory()->create();
        $equipment = Equipment::factory()->create(['status' => EquipmentStatus::Available]);
        $staff = Staff::factory()->create();

        $this->actingAs($user)->get(route('rentals.create'))->assertOk();

        $response = $this->actingAs($user)->post(route('rentals.store'), [
            'equipment_id' => $equipment->id,
            'staff_id' => $staff->id,
        ]);

        $response->assertRedirect(route('rentals.index'));
        $response->assertSessionHas('status', 'Checked out.');

        $this->assertSame(EquipmentStatus::CheckedOut, $equipment->fresh()->status);
        $this->assertDatabaseHas('rentals', [
            'equipment_id' => $equipment->id,
            'staff_id' => $staff->id,
            'status' => RentalStatus::Active->value,
        ]);
    }

    public function test_checkout_blocked_at_4th_active_rental_flashes_error(): void
    {
        $user = User::factory()->create();
        $staff = Staff::factory()->create();
        Rental::factory()->for($staff)->count(3)->create(['status' => RentalStatus::Active]);
        $equipment = Equipment::factory()->create(['status' => EquipmentStatus::Available]);

        $response = $this->actingAs($user)->post(route('rentals.store'), [
            'equipment_id' => $equipment->id,
            'staff_id' => $staff->id,
        ]);

        $response->assertSessionHasErrors('rental');
        $this->assertSame(EquipmentStatus::Available, $equipment->fresh()->status);
    }

    public function test_checkout_of_unavailable_equipment_flashes_visible_error(): void
    {
        $user = User::factory()->create();
        $equipment = Equipment::factory()->create(['status' => EquipmentStatus::CheckedOut]);
        $staff = Staff::factory()->create();

        $response = $this->actingAs($user)->from(route('rentals.create'))->post(route('rentals.store'), [
            'equipment_id' => $equipment->id,
            'staff_id' => $staff->id,
        ]);

        $response->assertSessionHasErrors('equipment');

        $follow = $this->actingAs($user)->get(route('rentals.create'));
        $follow->assertSeeText('is not available for checkout');
    }

    public function test_return_flow_sets_returned_and_equipment_available(): void
    {
        $user = User::factory()->create();
        $equipment = Equipment::factory()->create(['status' => EquipmentStatus::CheckedOut]);
        $rental = Rental::factory()->for($equipment)->create([
            'status' => RentalStatus::Active,
            'due_at' => now()->addDays(2),
        ]);

        $response = $this->actingAs($user)->post(route('rentals.return', $rental), [
            'condition' => Condition::Good->value,
        ]);

        $response->assertRedirect(route('rentals.index'));
        $response->assertSessionHas('status', 'Returned.');

        $this->assertSame(RentalStatus::Returned, $rental->fresh()->status);
        $this->assertSame(EquipmentStatus::Available, $equipment->fresh()->status);
    }

    public function test_return_flow_surfaces_late_fee_when_overdue(): void
    {
        $user = User::factory()->create();
        $equipment = Equipment::factory()->create(['status' => EquipmentStatus::CheckedOut]);
        $rental = Rental::factory()->for($equipment)->create([
            'status' => RentalStatus::Active,
            'due_at' => now()->subDays(2),
        ]);

        $response = $this->actingAs($user)->post(route('rentals.return', $rental), [
            'condition' => Condition::Good->value,
        ]);

        $response->assertSessionHas('status', 'Returned. Late fee: $10.00.');
    }

    public function test_returning_an_already_returned_rental_flashes_error_instead_of_500(): void
    {
        $user = User::factory()->create();
        $rental = Rental::factory()->create([
            'status' => RentalStatus::Returned,
            'returned_at' => now(),
        ]);

        $response = $this->actingAs($user)->post(route('rentals.return', $rental), [
            'condition' => Condition::Good->value,
        ]);

        $response->assertSessionHasErrors('rental');
    }

    public function test_index_flags_overdue_rentals(): void
    {
        $user = User::factory()->create();
        $overdue = Rental::factory()->create([
            'status' => RentalStatus::Active,
            'due_at' => now()->subDays(3),
        ]);
        $onTime = Rental::factory()->create([
            'status' => RentalStatus::Active,
            'due_at' => now()->addDays(3),
        ]);

        $response = $this->actingAs($user)->get(route('rentals.index'));

        $response->assertOk();
        $response->assertSeeText('3 days overdue');
        $response->assertSeeText($overdue->equipment->serial_no);
        $response->assertSeeText($onTime->equipment->serial_no);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('rentals.index'))->assertRedirect(route('login'));
    }
}
