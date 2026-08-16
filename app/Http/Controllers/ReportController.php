<?php

namespace App\Http\Controllers;

use App\Enums\RentalStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ReportController extends Controller
{
    public const OVERDUE_SQL = 'SELECT rentals.id, equipment.name AS equipment_name, staff.name AS staff_name, rentals.due_at
             FROM rentals
             JOIN equipment ON equipment.id = rentals.equipment_id
             JOIN staff ON staff.id = rentals.staff_id
             WHERE rentals.status = :status AND rentals.due_at < :now';

    public function overdue(): View
    {
        $pdo = DB::connection()->getPdo();

        $statement = $pdo->prepare(self::OVERDUE_SQL);

        $statement->execute([
            'status' => RentalStatus::Active->value,
            'now' => now()->toDateTimeString(),
        ]);

        $rows = $statement->fetchAll(\PDO::FETCH_ASSOC);

        return view('reports.overdue', ['rows' => $rows]);
    }
}
