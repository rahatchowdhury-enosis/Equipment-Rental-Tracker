<?php

namespace App\Contracts;

interface RentableInterface
{
    public function isAvailable(): bool;

    public function markCheckedOut(): void;

    public function markAvailable(): void;
}
