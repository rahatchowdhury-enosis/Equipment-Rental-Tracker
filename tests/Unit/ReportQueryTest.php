<?php

namespace Tests\Unit;

use App\Enums\RentalStatus;
use App\Http\Controllers\ReportController;
use App\Models\Rental;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ReportQueryTest extends TestCase
{
    use RefreshDatabase;

    public function test_overdue_raw_query_returns_expected_row_shape(): void
    {
        $rental = Rental::factory()->overdue()->create();

        $pdo = DB::connection()->getPdo();
        $statement = $pdo->prepare(ReportController::OVERDUE_SQL);
        $statement->execute([
            'status' => RentalStatus::Active->value,
            'now' => now()->toDateTimeString(),
        ]);

        $rows = $statement->fetchAll(\PDO::FETCH_ASSOC);

        $this->assertCount(1, $rows);
        $this->assertSame($rental->id, $rows[0]['id']);
        $this->assertSame($rental->equipment->name, $rows[0]['equipment_name']);
        $this->assertSame($rental->staff->name, $rows[0]['staff_name']);
        $this->assertArrayHasKey('due_at', $rows[0]);
    }
}
