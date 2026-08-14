<?php

namespace Tests\Unit\Requests;

use App\Enums\Role;
use App\Http\Requests\StoreStaffRequest;
use App\Models\Staff;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class StoreStaffRequestTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Build request rules(), optionally bound to a route parameter
     * (simulates update route-model-binding for the ignore() clause).
     */
    private function rulesFor(?Model $boundStaff = null): array
    {
        $request = new StoreStaffRequest;

        if ($boundStaff) {
            $route = new class($boundStaff)
            {
                public function __construct(private Model $model) {}

                public function parameter($name, $default = null)
                {
                    return $name === 'staff' ? $this->model : $default;
                }
            };
            $request->setRouteResolver(fn () => $route);
        }

        return $request->rules();
    }

    /**
     * Build request rules() with a spoofed `staff` request field (not a
     * route param) — proves ignore() reads the route binding, not raw input.
     */
    private function rulesWithSpoofedInput(int $spoofedId): array
    {
        $request = new StoreStaffRequest;
        $request->merge(['staff' => $spoofedId]);

        return $request->rules();
    }

    /**
     * Test valid payload passes validation.
     */
    public function test_valid_payload_passes_validation(): void
    {
        $validator = Validator::make([
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'role' => Role::Staff->value,
        ], $this->rulesFor());

        $this->assertTrue($validator->passes());
    }

    /**
     * Test duplicate email on create is rejected.
     */
    public function test_duplicate_email_on_create_is_rejected(): void
    {
        Staff::factory()->create(['email' => 'jane@example.com']);

        $validator = Validator::make([
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'role' => Role::Staff->value,
        ], $this->rulesFor());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('email', $validator->errors()->toArray());
    }

    /**
     * Test same email on update-without-change is accepted (ignore current record).
     */
    public function test_unchanged_email_on_update_is_accepted(): void
    {
        $staff = Staff::factory()->create(['email' => 'jane@example.com']);

        $validator = Validator::make([
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'role' => Role::Staff->value,
        ], $this->rulesFor($staff));

        $this->assertTrue($validator->passes());
    }

    /**
     * Test email belonging to a different record is still rejected
     * while editing (ignore() must not suppress the constraint entirely).
     */
    public function test_email_taken_by_another_record_is_rejected_on_update(): void
    {
        $editing = Staff::factory()->create(['email' => 'jane@example.com']);
        Staff::factory()->create(['email' => 'john@example.com']);

        $validator = Validator::make([
            'name' => 'Jane Doe',
            'email' => 'john@example.com',
            'role' => Role::Staff->value,
        ], $this->rulesFor($editing));

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('email', $validator->errors()->toArray());
    }

    /**
     * Test a spoofed `staff` request field cannot be used to bypass the
     * unique check on create — ignore() must key off the bound route
     * parameter, not `$this->staff` (which reads request input first).
     */
    public function test_spoofed_staff_field_does_not_bypass_unique_check(): void
    {
        $other = Staff::factory()->create(['email' => 'john@example.com']);

        $validator = Validator::make([
            'name' => 'Jane Doe',
            'email' => 'john@example.com',
            'role' => Role::Staff->value,
        ], $this->rulesWithSpoofedInput($other->id));

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('email', $validator->errors()->toArray());
    }

    /**
     * Test invalid role enum value is rejected.
     */
    public function test_invalid_role_value_is_rejected(): void
    {
        $validator = Validator::make([
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'role' => 'manager',
        ], $this->rulesFor());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('role', $validator->errors()->toArray());
    }
}
