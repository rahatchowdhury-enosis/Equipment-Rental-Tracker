<?php

namespace Tests\Unit\Models;

use App\Contracts\RentableInterface;
use App\Enums\EquipmentStatus;
use App\Models\Equipment;
use App\Models\Rental;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EquipmentModelTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test Equipment::available() returns only status=available rows.
     */
    public function test_available_scope_returns_only_available_equipment(): void
    {
        Equipment::factory()->create(['status' => EquipmentStatus::Available]);
        Equipment::factory()->create(['status' => EquipmentStatus::Available]);
        Equipment::factory()->create(['status' => EquipmentStatus::CheckedOut]);

        $available = Equipment::available()->get();

        $this->assertCount(2, $available);
        $available->each(function ($equipment) {
            $this->assertEquals(EquipmentStatus::Available, $equipment->status);
        });
    }

    /**
     * Test Equipment::__toString() matches "{$name} ({$serial_no})".
     */
    public function test_to_string_format(): void
    {
        $equipment = Equipment::factory()->create([
            'name' => 'Power Drill',
            'serial_no' => 'DRL-123456',
        ]);

        $expected = 'Power Drill (DRL-123456)';
        $this->assertEquals($expected, (string) $equipment);
    }

    /**
     * Test cloning with serial_no reassignment succeeds.
     */
    public function test_clone_with_new_serial_number_saves_successfully(): void
    {
        $original = Equipment::factory()->create([
            'name' => 'Drill',
            'serial_no' => 'DRL-001',
            'status' => EquipmentStatus::Available,
        ]);

        $copy = $original->replicate();
        $copy->serial_no = 'DRL-002';
        $copy->save();

        $this->assertNotNull($copy->id);
        $this->assertNotEquals($original->id, $copy->id);
        $this->assertEquals('DRL-002', $copy->serial_no);
        $this->assertEquals('DRL-001', $original->serial_no);
    }

    /**
     * Test cloning without serial_no reassignment throws unique constraint exception.
     */
    public function test_clone_without_new_serial_number_throws_exception(): void
    {
        $original = Equipment::factory()->create([
            'name' => 'Drill',
            'serial_no' => 'DRL-999',
        ]);

        $copy = $original->replicate();
        // Do not reassign serial_no; should violate unique constraint

        $this->expectException(QueryException::class);
        $copy->save();
    }

    /**
     * Test duplicateWithSerial helper method.
     */
    public function test_duplicate_with_serial_creates_new_equipment(): void
    {
        $original = Equipment::factory()->create([
            'name' => 'Power Drill',
            'serial_no' => 'DRL-100',
            'category' => 'Tools',
            'status' => EquipmentStatus::Available,
        ]);

        $duplicate = $original->duplicateWithSerial('DRL-101');

        $this->assertNotNull($duplicate->id);
        $this->assertNotEquals($original->id, $duplicate->id);
        $this->assertEquals('DRL-101', $duplicate->serial_no);
        $this->assertEquals('Power Drill', $duplicate->name);
        $this->assertEquals('Tools', $duplicate->category);
    }

    /**
     * Test Equipment has many rentals.
     */
    public function test_equipment_has_many_rentals(): void
    {
        $equipment = Equipment::factory()->create();
        Rental::factory()->for($equipment)->count(3)->create();

        $this->assertCount(3, $equipment->rentals);
        $equipment->rentals->each(function ($rental) use ($equipment) {
            $this->assertEquals($equipment->id, $rental->equipment_id);
        });
    }

    /**
     * Raw `clone` copies full model state, including `exists` — saving it
     * as-is issues an UPDATE against the original row instead of inserting
     * a new one, silently overwriting the original.
     */
    public function test_php_clone_without_reset_overwrites_original(): void
    {
        $original = Equipment::factory()->create([
            'name' => 'Excavator',
            'serial_no' => 'EXC-500',
        ]);

        $copy = clone $original;
        $copy->serial_no = 'EXC-999';
        $copy->save();

        $this->assertSame('EXC-999', $original->fresh()->serial_no);
        $this->assertSame(1, Equipment::count());
    }

    /**
     * Test raw PHP clone (outside replicate()) demonstrates the
     * "Working with Objects" topic: to persist as a distinct row, both
     * `exists` must be reset to false AND the primary key attribute must
     * be unset (nulling `id` alone still binds NULL into the insert,
     * which Postgres rejects for a `bigserial` primary key).
     */
    public function test_php_clone_pattern_demonstrates_shallow_copy(): void
    {
        $original = Equipment::factory()->create([
            'name' => 'Excavator',
            'serial_no' => 'EXC-500',
            'status' => EquipmentStatus::Available,
        ]);

        $copy = clone $original;
        $copy->exists = false;
        $copy->offsetUnset('id');
        $copy->serial_no = 'EXC-501';
        $copy->save();

        $this->assertCount(2, Equipment::whereIn('serial_no', ['EXC-500', 'EXC-501'])->get());
        $this->assertEquals('EXC-500', $original->fresh()->serial_no);
    }

    /**
     * Test Equipment implements RentableInterface.
     */
    public function test_equipment_implements_rentable_interface(): void
    {
        $this->assertInstanceOf(RentableInterface::class, new Equipment);
    }

    /**
     * Test isAvailable() reflects the status enum correctly.
     */
    public function test_is_available_reflects_status_enum(): void
    {
        $available = Equipment::factory()->make(['status' => EquipmentStatus::Available]);
        $checkedOut = Equipment::factory()->make(['status' => EquipmentStatus::CheckedOut]);

        $this->assertTrue($available->isAvailable());
        $this->assertFalse($checkedOut->isAvailable());
    }

    /**
     * Test markCheckedOut()/markAvailable() mutate status without saving.
     */
    public function test_mark_checked_out_and_mark_available_mutate_status_only(): void
    {
        $equipment = Equipment::factory()->create(['status' => EquipmentStatus::Available]);

        $equipment->markCheckedOut();
        $this->assertEquals(EquipmentStatus::CheckedOut, $equipment->status);
        $this->assertEquals(EquipmentStatus::Available, $equipment->fresh()->status);

        $equipment->markAvailable();
        $this->assertEquals(EquipmentStatus::Available, $equipment->status);
    }
}
