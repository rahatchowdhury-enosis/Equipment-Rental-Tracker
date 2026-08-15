<?php

namespace App\Exceptions;

use App\Models\Equipment;
use RuntimeException;

/**
 * Rendered by bootstrap/app.php's renderable() handler for HTTP requests only;
 * CLI callers (Artisan commands, tinker) must catch this exception themselves.
 */
class EquipmentNotAvailableException extends RuntimeException
{
    public static function forEquipment(Equipment $equipment): self
    {
        return new self("\"{$equipment}\" is not available for checkout.");
    }
}
