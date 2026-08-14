<?php

namespace App\Models;

use App\Enums\Condition;
use App\Enums\EquipmentStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Equipment extends Model
{
    use HasFactory;

    const TABLE = 'equipment';

    protected $table = self::TABLE;

    protected $fillable = ['name', 'category', 'serial_no', 'photo', 'condition', 'status'];

    protected function casts(): array
    {
        return [
            'condition' => Condition::class,
            'status' => EquipmentStatus::class,
        ];
    }

    public function rentals(): HasMany
    {
        return $this->hasMany(Rental::class);
    }

    public static function available(): Builder
    {
        return static::where('status', EquipmentStatus::Available);
    }

    public function __toString(): string
    {
        return "{$this->name} ({$this->serial_no})";
    }

    /**
     * Duplicate equipment with a new serial number.
     * Example of replicate() pattern for catalog duplication.
     *
     * @param  string  $newSerial  The new serial number for the duplicate.
     * @return static The new equipment instance.
     */
    public function duplicateWithSerial(string $newSerial): static
    {
        $copy = $this->replicate();
        $copy->serial_no = $newSerial;
        $copy->save();

        return $copy;
    }
}
