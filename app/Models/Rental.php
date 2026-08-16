<?php

namespace App\Models;

use App\Enums\RentalStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Rental extends Model
{
    use HasFactory;

    const TABLE = 'rentals';

    const MAX_EXTENSIONS = 1;

    const LATE_FEE_CENTS_PER_DAY = 500;

    protected $table = self::TABLE;

    protected $fillable = ['equipment_id', 'staff_id', 'checked_out_at', 'due_at', 'returned_at', 'status'];

    protected function casts(): array
    {
        return [
            'checked_out_at' => 'datetime',
            'due_at' => 'datetime',
            'returned_at' => 'datetime',
            'status' => RentalStatus::class,
        ];
    }

    /**
     * Fee owed right now: final once returned, still accruing while active.
     */
    public function lateFeeCents(): int
    {
        $daysLate = max(0, days_between($this->due_at, $this->returned_at ?? now()));

        return $daysLate * self::LATE_FEE_CENTS_PER_DAY;
    }

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class);
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }
}
