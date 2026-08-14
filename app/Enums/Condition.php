<?php

namespace App\Enums;

enum Condition: string
{
    case Good = 'good';
    case Damaged = 'damaged';
    case Lost = 'lost';

    public function label(): string
    {
        return match ($this) {
            self::Good => 'Good',
            self::Damaged => 'Damaged',
            self::Lost => 'Lost',
        };
    }
}
