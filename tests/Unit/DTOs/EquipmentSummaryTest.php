<?php

namespace Tests\Unit\DTOs;

use App\DTOs\EquipmentSummary;
use Tests\TestCase;

/**
 * One-off subclass to prove late static binding: EquipmentSummaryDebug::create()
 * must return EquipmentSummaryDebug, not EquipmentSummary. Test-only, not shipped app code.
 */
class EquipmentSummaryDebug extends EquipmentSummary {}

class EquipmentSummaryTest extends TestCase
{
    public function test_create_returns_equipment_summary(): void
    {
        $summary = EquipmentSummary::create('Canon EOS R5', 'available');

        $this->assertInstanceOf(EquipmentSummary::class, $summary);
        $this->assertSame('Canon EOS R5', $summary->name);
        $this->assertSame('available', $summary->status);
    }

    public function test_subclass_create_uses_late_static_binding(): void
    {
        $summary = EquipmentSummaryDebug::create('DeWalt Drill', 'checked_out');

        $this->assertInstanceOf(EquipmentSummaryDebug::class, $summary);
    }
}
