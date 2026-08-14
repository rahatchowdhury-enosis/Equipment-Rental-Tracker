<?php

namespace App\Enums;

enum EquipmentStatus: string
{
    case Available = 'available';
    case CheckedOut = 'checked_out';

    public function label(): string
    {
        return match ($this) {
            self::Available => 'Available',
            self::CheckedOut => 'Checked Out',
        };
    }
}
