<?php

namespace App\Services;

use App\Traits\Loggable;

/**
 * handle() is a loose-arity hook to satisfy the abstract-class contract shared
 * across services; concrete services (e.g. RentalService) expose their own
 * typed methods (checkout(), return()) as the primary API, not handle().
 */
abstract class BaseService
{
    use Loggable;

    abstract public function handle(...$args);
}
