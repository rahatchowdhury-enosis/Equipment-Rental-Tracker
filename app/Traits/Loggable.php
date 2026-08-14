<?php

namespace App\Traits;

use Illuminate\Support\Facades\Log;

trait Loggable
{
    protected function logAction(string $message, array $context = []): void
    {
        Log::info(static::class.': '.$message, $context);
    }
}
