<?php

namespace App\DTOs;

class EquipmentSummary
{
    private function __construct(
        public readonly string $name,
        public readonly string $status,
    ) {}

    // Late static binding: static::create() in a subclass returns the subclass, not EquipmentSummary
    public static function create(string $name, string $status): static
    {
        return new static($name, $status);
    }
}
