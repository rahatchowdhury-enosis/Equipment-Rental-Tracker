<?php

namespace Tests\Unit\Exceptions;

use App\Exceptions\EquipmentNotAvailableException;
use App\Models\Equipment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EquipmentNotAvailableExceptionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test forEquipment() message contains the equipment's __toString() output.
     */
    public function test_for_equipment_message_contains_equipment_to_string(): void
    {
        $equipment = Equipment::factory()->create([
            'name' => 'Power Drill',
            'serial_no' => 'DRL-123456',
        ]);

        $exception = EquipmentNotAvailableException::forEquipment($equipment);

        $this->assertStringContainsString((string) $equipment, $exception->getMessage());
        $this->assertEquals('"Power Drill (DRL-123456)" is not available for checkout.', $exception->getMessage());
    }
}
