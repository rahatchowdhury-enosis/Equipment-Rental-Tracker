<?php

namespace Tests\Unit\Controllers;

use App\Http\Controllers\StaffController;
use App\Models\Staff;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class StaffControllerTest extends TestCase
{
    use RefreshDatabase;

    private function currentStaffOrGuest(): Staff
    {
        $method = new \ReflectionMethod(StaffController::class, 'currentStaffOrGuest');
        $method->setAccessible(true);

        return $method->invoke(new StaffController);
    }

    public function test_returns_real_staff_when_user_has_a_linked_row(): void
    {
        $user = User::factory()->create();
        $staff = Staff::factory()->for($user)->create();
        Auth::login($user);

        $resolved = $this->currentStaffOrGuest();

        $this->assertSame($staff->id, $resolved->id);
        $this->assertTrue($resolved->exists);
    }

    public function test_returns_guest_null_object_when_user_has_no_linked_staff_row(): void
    {
        $user = User::factory()->create();
        Auth::login($user);

        $guest = $this->currentStaffOrGuest();

        $this->assertSame('Guest', $guest->name);
        $this->assertFalse($guest->exists);
        $this->assertCount(0, $guest->activeRentals);
    }

    public function test_returns_guest_when_unauthenticated_even_if_an_unlinked_staff_row_exists(): void
    {
        Staff::factory()->create(['user_id' => null]);

        $guest = $this->currentStaffOrGuest();

        $this->assertSame('Guest', $guest->name);
        $this->assertFalse($guest->exists);
    }
}
