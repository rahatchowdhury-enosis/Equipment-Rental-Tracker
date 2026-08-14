<?php

namespace Tests\Unit\Traits;

use App\Traits\Loggable;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

/**
 * Test-only fixture using Loggable, so logAction()'s static::class prefix
 * can be asserted against a known class name.
 */
class LoggableFixture
{
    use Loggable;

    public function run(): void
    {
        $this->logAction('did a thing', ['id' => 1]);
    }
}

class LoggableTest extends TestCase
{
    public function test_log_action_writes_to_log_channel(): void
    {
        Log::spy();

        (new LoggableFixture)->run();

        Log::shouldHaveReceived('info')
            ->once()
            ->withArgs(fn (string $message, array $context) => $message === LoggableFixture::class.': did a thing' && $context === ['id' => 1]
            );
    }
}
