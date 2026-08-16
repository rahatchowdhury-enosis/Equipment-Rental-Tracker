<?php

namespace Tests\Feature;

use App\Models\Equipment;
use App\Models\Rental;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_overdue_report_lists_only_overdue_rentals(): void
    {
        $user = User::factory()->create();
        $overdueEquipment = Equipment::factory()->create(['name' => 'Overdue Camera']);
        $onTimeEquipment = Equipment::factory()->create(['name' => 'On Time Drill']);
        $returnedEquipment = Equipment::factory()->create(['name' => 'Returned Mixer']);
        Rental::factory()->overdue()->for($overdueEquipment)->create();
        Rental::factory()->for($onTimeEquipment)->create();
        Rental::factory()->returned()->for($returnedEquipment)->create();

        $response = $this->actingAs($user)->get(route('reports.overdue'));

        $response->assertOk();
        $response->assertSeeText('Overdue Camera');
        $response->assertDontSeeText('On Time Drill');
        $response->assertDontSeeText('Returned Mixer');
    }

    public function test_overdue_report_shows_empty_state_when_none_overdue(): void
    {
        $user = User::factory()->create();
        Rental::factory()->create();

        $response = $this->actingAs($user)->get(route('reports.overdue'));

        $response->assertOk();
        $response->assertSeeText('No overdue equipment.');
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('reports.overdue'))->assertRedirect(route('login'));
    }
}
