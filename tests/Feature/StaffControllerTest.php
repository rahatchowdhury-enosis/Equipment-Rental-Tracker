<?php

namespace Tests\Feature;

use App\Enums\RentalStatus;
use App\Enums\Role;
use App\Models\Rental;
use App\Models\Staff;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StaffControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_full_crud_cycle(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('staff.index'))->assertOk();
        $this->actingAs($user)->get(route('staff.create'))->assertOk();

        $store = $this->actingAs($user)->post('/staff', [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'role' => Role::Staff->value,
        ]);
        $store->assertRedirect(route('staff.index'));
        $store->assertSessionHas('status', 'staff-created');

        $staff = Staff::where('email', 'jane@example.com')->firstOrFail();

        $this->actingAs($user)->get(route('staff.show', $staff))->assertOk();
        $this->actingAs($user)->get(route('staff.edit', $staff))->assertOk();

        $update = $this->actingAs($user)->put(route('staff.update', $staff), [
            'name' => 'Jane Smith',
            'email' => 'jane@example.com',
            'role' => Role::Admin->value,
        ]);
        $update->assertRedirect(route('staff.index'));
        $update->assertSessionHas('status', 'staff-updated');
        $this->assertSame('Jane Smith', $staff->refresh()->name);

        $destroy = $this->actingAs($user)->delete(route('staff.destroy', $staff));
        $destroy->assertRedirect(route('staff.index'));
        $destroy->assertSessionHas('status', 'staff-deleted');
        $this->assertNull($staff->fresh());
    }

    public function test_email_collision_is_rejected(): void
    {
        $user = User::factory()->create();
        Staff::factory()->create(['email' => 'taken@example.com']);

        $response = $this->actingAs($user)->post('/staff', [
            'name' => 'Jane Doe',
            'email' => 'taken@example.com',
            'role' => Role::Staff->value,
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_email_taken_by_another_record_is_rejected_on_update(): void
    {
        $user = User::factory()->create();
        $staffA = Staff::factory()->create(['email' => 'a@example.com']);
        $staffB = Staff::factory()->create(['email' => 'b@example.com']);

        $response = $this->actingAs($user)->put(route('staff.update', $staffA), [
            'name' => $staffA->name,
            'email' => $staffB->email,
            'role' => Role::Staff->value,
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_user_id_cannot_be_mass_assigned_via_the_request(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $this->actingAs($user)->post('/staff', [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'role' => Role::Staff->value,
            'user_id' => $otherUser->id,
        ]);

        $staff = Staff::where('email', 'jane@example.com')->firstOrFail();
        $this->assertNull($staff->user_id);

        $this->actingAs($user)->put(route('staff.update', $staff), [
            'name' => $staff->name,
            'email' => $staff->email,
            'role' => Role::Staff->value,
            'user_id' => $otherUser->id,
        ]);

        $this->assertNull($staff->fresh()->user_id);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('staff.index'))->assertRedirect(route('login'));
    }

    public function test_deleting_staff_with_an_active_rental_is_blocked(): void
    {
        $user = User::factory()->create();
        $staff = Staff::factory()->create();
        Rental::factory()->create(['staff_id' => $staff->id, 'status' => RentalStatus::Active]);

        $response = $this->actingAs($user)->delete(route('staff.destroy', $staff));

        $response->assertRedirect(route('staff.index'));
        $response->assertSessionHas('error');
        $this->assertNotNull($staff->fresh());
    }
}
