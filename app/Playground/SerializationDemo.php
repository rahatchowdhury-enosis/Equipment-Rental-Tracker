<?php

namespace App\Playground;

class SerializationDemo
{
    /**
     * @return array{serialized: string, restored: RentalHistoryRecord}
     */
    public static function run(): array
    {
        $record = new RentalHistoryRecord('Canon EOS R5', 'Jane Doe', new \DateTimeImmutable('2026-08-01'));

        $serialized = serialize($record);

        // Safe here: we only unserialize a string we just produced ourselves.
        // Never call unserialize() on untrusted/external input — PHP object
        // injection is a known footgun (use json_decode instead for that).
        $restored = unserialize($serialized);

        return compact('serialized', 'restored');
    }
}
