<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\Condition;
use App\Enums\RentalStatus;
use App\Exceptions\EquipmentNotAvailableException;
use App\Models\Equipment;
use App\Models\Rental;
use App\Models\Staff;
use Illuminate\Support\Facades\DB;

class RentalService extends BaseService
{
    public function handle(...$args)
    {
        throw new \BadMethodCallException('Use checkout()/returnRental() directly.');
    }

    public function checkout(Equipment $equipment, Staff $staff): Rental
    {
        $rental = DB::transaction(function () use ($equipment, $staff) {
            $equipment = Equipment::whereKey($equipment->id)->lockForUpdate()->firstOrFail();

            if (! $equipment->isAvailable()) {
                throw EquipmentNotAvailableException::forEquipment($equipment);
            }

            if ($staff->activeRentals()->lockForUpdate()->get()->count() >= 3) {
                throw new \DomainException("{$staff->name} already has 3 active rentals.");
            }

            $equipment->markCheckedOut();
            $equipment->save();

            return Rental::create([
                'equipment_id' => $equipment->id,
                'staff_id' => $staff->id,
                'checked_out_at' => now(),
                'due_at' => now()->addDays(7),
                'status' => RentalStatus::Active,
            ]);
        });

        $this->logAction('Checked out', ['rental_id' => $rental->id]);

        return $rental;
    }

    public function returnRental(Rental $rental, Condition $condition): Rental
    {
        $now = now();

        $rental = DB::transaction(function () use ($rental, $condition, $now) {
            $rental = Rental::whereKey($rental->id)->lockForUpdate()->firstOrFail();

            if ($rental->status === RentalStatus::Returned) {
                throw new \DomainException('Already returned.');
            }

            $rental->returned_at = $now;
            $rental->status = RentalStatus::Returned;
            $rental->save();

            $rental->equipment->condition = $condition;
            $rental->equipment->markAvailable();
            $rental->equipment->save();

            return $rental;
        });

        $lateFeeCents = $this->calculateLateFee($rental);

        $this->logAction('Returned', ['rental_id' => $rental->id, 'late_fee_cents' => $lateFeeCents]);

        return $rental;
    }

    public function calculateLateFee(Rental $rental): int
    {
        $daysLate = max(0, days_between($rental->due_at, $rental->returned_at ?? now()));

        return $daysLate * 500;
    }
}
