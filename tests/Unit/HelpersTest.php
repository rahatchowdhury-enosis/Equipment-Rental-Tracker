<?php

namespace Tests\Unit;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

class HelpersTest extends TestCase
{
    public function test_format_late_fee_formats_cents_as_dollars(): void
    {
        $this->assertSame('$5.50', format_late_fee(550));
        $this->assertSame('$0.00', format_late_fee(0));
        $this->assertSame('$1,234.56', format_late_fee(123456));
    }

    public function test_days_between_counts_whole_days(): void
    {
        $from = new DateTimeImmutable('2026-01-01');
        $to = new DateTimeImmutable('2026-01-05');

        $this->assertSame(4, days_between($from, $to));
    }

    public function test_days_between_across_dst_boundary(): void
    {
        $from = new DateTimeImmutable('2026-03-06', new \DateTimeZone('America/New_York'));
        $to = new DateTimeImmutable('2026-03-09', new \DateTimeZone('America/New_York'));

        $this->assertSame(3, days_between($from, $to));
    }

    public function test_days_between_is_negative_when_from_is_after_to(): void
    {
        $due = new DateTimeImmutable('2026-01-01');
        $now = new DateTimeImmutable('2026-01-05');

        $this->assertSame(-4, days_between($now, $due));
    }

    public function test_days_between_ignores_time_of_day(): void
    {
        $from = new DateTimeImmutable('2026-01-01 23:00');
        $to = new DateTimeImmutable('2026-01-02 01:00');

        $this->assertSame(1, days_between($from, $to));
    }
}
