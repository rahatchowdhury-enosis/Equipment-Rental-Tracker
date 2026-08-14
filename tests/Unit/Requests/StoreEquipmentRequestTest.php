<?php

namespace Tests\Unit\Requests;

use App\Enums\Condition;
use App\Enums\EquipmentStatus;
use App\Http\Requests\StoreEquipmentRequest;
use App\Models\Equipment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class StoreEquipmentRequestTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Build request rules(), optionally bound to a route parameter
     * (simulates update route-model-binding for the ignore() clause).
     */
    private function rulesFor(?Model $boundEquipment = null): array
    {
        $request = new StoreEquipmentRequest;

        if ($boundEquipment) {
            $route = new class($boundEquipment)
            {
                public function __construct(private Model $model) {}

                public function parameter($name, $default = null)
                {
                    return $name === 'equipment' ? $this->model : $default;
                }
            };
            $request->setRouteResolver(fn () => $route);
        }

        return $request->rules();
    }

    /**
     * Build request rules() with a spoofed `equipment` request field (not a
     * route param) — proves ignore() reads the route binding, not raw input.
     */
    private function rulesWithSpoofedInput(int $spoofedId): array
    {
        $request = new StoreEquipmentRequest;
        $request->merge(['equipment' => $spoofedId]);

        return $request->rules();
    }

    /**
     * Test valid payload passes validation.
     */
    public function test_valid_payload_passes_validation(): void
    {
        $validator = Validator::make([
            'name' => 'Power Drill',
            'category' => 'Tools',
            'serial_no' => 'DRL-001',
            'condition' => Condition::Good->value,
            'status' => EquipmentStatus::Available->value,
        ], $this->rulesFor());

        $this->assertTrue($validator->passes());
    }

    /**
     * Test duplicate serial_no on create is rejected.
     */
    public function test_duplicate_serial_no_on_create_is_rejected(): void
    {
        Equipment::factory()->create(['serial_no' => 'DRL-001']);

        $validator = Validator::make([
            'name' => 'Power Drill',
            'category' => 'Tools',
            'serial_no' => 'DRL-001',
            'condition' => Condition::Good->value,
            'status' => EquipmentStatus::Available->value,
        ], $this->rulesFor());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('serial_no', $validator->errors()->toArray());
    }

    /**
     * Test same serial_no on update-without-change is accepted (ignore current record).
     */
    public function test_unchanged_serial_no_on_update_is_accepted(): void
    {
        $equipment = Equipment::factory()->create(['serial_no' => 'DRL-001']);

        $validator = Validator::make([
            'name' => 'Power Drill',
            'category' => 'Tools',
            'serial_no' => 'DRL-001',
            'condition' => Condition::Good->value,
            'status' => EquipmentStatus::Available->value,
        ], $this->rulesFor($equipment));

        $this->assertTrue($validator->passes());
    }

    /**
     * Test serial_no belonging to a different record is still rejected
     * while editing (ignore() must not suppress the constraint entirely).
     */
    public function test_serial_no_taken_by_another_record_is_rejected_on_update(): void
    {
        $editing = Equipment::factory()->create(['serial_no' => 'DRL-001']);
        Equipment::factory()->create(['serial_no' => 'DRL-002']);

        $validator = Validator::make([
            'name' => 'Power Drill',
            'category' => 'Tools',
            'serial_no' => 'DRL-002',
            'condition' => Condition::Good->value,
            'status' => EquipmentStatus::Available->value,
        ], $this->rulesFor($editing));

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('serial_no', $validator->errors()->toArray());
    }

    /**
     * Test a spoofed `equipment` request field cannot be used to bypass the
     * unique check on create — ignore() must key off the bound route
     * parameter, not `$this->equipment` (which reads request input first).
     */
    public function test_spoofed_equipment_field_does_not_bypass_unique_check(): void
    {
        $other = Equipment::factory()->create(['serial_no' => 'DRL-002']);

        $validator = Validator::make([
            'name' => 'Power Drill',
            'category' => 'Tools',
            'serial_no' => 'DRL-002',
            'condition' => Condition::Good->value,
            'status' => EquipmentStatus::Available->value,
        ], $this->rulesWithSpoofedInput($other->id));

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('serial_no', $validator->errors()->toArray());
    }

    /**
     * Test invalid condition/status enum value is rejected.
     */
    public function test_invalid_enum_values_are_rejected(): void
    {
        $validator = Validator::make([
            'name' => 'Power Drill',
            'category' => 'Tools',
            'serial_no' => 'DRL-002',
            'condition' => 'rusty',
            'status' => 'in_use',
        ], $this->rulesFor());

        $this->assertTrue($validator->fails());
        $errors = $validator->errors()->toArray();
        $this->assertArrayHasKey('condition', $errors);
        $this->assertArrayHasKey('status', $errors);
    }
}
