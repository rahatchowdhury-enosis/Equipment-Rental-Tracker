<?php

namespace App\Models;

use App\Enums\RentalStatus;
use App\Enums\Role;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Staff extends Model
{
    use HasFactory;

    const TABLE = 'staff';

    protected $table = self::TABLE;

    protected $fillable = ['name', 'email', 'role', 'user_id'];

    protected function casts(): array
    {
        return ['role' => Role::class];
    }

    public function rentals(): HasMany
    {
        return $this->hasMany(Rental::class);
    }

    public function activeRentals(): HasMany
    {
        return $this->rentals()->where('status', RentalStatus::Active);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
