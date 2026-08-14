<?php

declare(strict_types=1);

if (! function_exists('format_late_fee')) {
    function format_late_fee(int $cents): string
    {
        return '$'.number_format($cents / 100, 2);
    }
}

if (! function_exists('days_between')) {
    function days_between(DateTimeInterface $from, DateTimeInterface $to): int
    {
        $fromDate = new DateTimeImmutable($from->format('Y-m-d'));
        $toDate = new DateTimeImmutable($to->format('Y-m-d'));
        $days = (int) $fromDate->diff($toDate)->format('%a');

        return $fromDate > $toDate ? -$days : $days;
    }
}
