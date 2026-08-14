<?php

namespace Tests\Unit\Models;

use App\Enums\Condition;
use App\Enums\Role;
use App\Models\Equipment;
use App\Models\Rental;
use App\Models\Staff;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class RentalModelTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test Rental belongs to Equipment.
     */
    public function test_rental_belongs_to_equipment(): void
    {
        $equipment = Equipment::factory()->create();
        $rental = Rental::factory()->for($equipment)->create();

        $this->assertEquals($equipment->id, $rental->equipment->id);
    }

    /**
     * Test Rental belongs to Staff.
     */
    public function test_rental_belongs_to_staff(): void
    {
        $staff = Staff::factory()->create();
        $rental = Rental::factory()->for($staff)->create();

        $this->assertEquals($staff->id, $rental->staff->id);
    }

    /**
     * Test Rental::TABLE actually configures the model's table binding.
     */
    public function test_rental_table_constant_matches_model_binding(): void
    {
        $this->assertSame(Rental::TABLE, (new Rental)->getTable());
    }

    /**
     * Test datetime and enum casts hydrate to the expected types.
     */
    public function test_rental_casts_hydrate_correctly(): void
    {
        $equipment = Equipment::factory()->create(['condition' => Condition::Good]);
        $staff = Staff::factory()->create(['role' => Role::Staff]);
        $rental = Rental::factory()->for($equipment)->for($staff)->create()->fresh();

        $this->assertInstanceOf(Carbon::class, $rental->checked_out_at);
        $this->assertInstanceOf(Carbon::class, $rental->due_at);
        $this->assertSame(Condition::Good, $equipment->fresh()->condition);
        $this->assertSame(Role::Staff, $staff->fresh()->role);
    }
}
