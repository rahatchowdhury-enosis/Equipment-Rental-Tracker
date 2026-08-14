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

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class);
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }
}
