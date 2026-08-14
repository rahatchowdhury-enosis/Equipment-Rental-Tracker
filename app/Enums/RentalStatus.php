<?php

namespace App\Enums;

enum RentalStatus: string
{
    case Active = 'active';
    case Returned = 'returned';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Active',
            self::Returned => 'Returned',
        };
    }
}
