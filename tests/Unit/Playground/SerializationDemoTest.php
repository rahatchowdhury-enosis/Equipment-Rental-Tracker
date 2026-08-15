<?php

namespace Tests\Unit\Playground;

use App\Playground\RentalHistoryRecord;
use App\Playground\SerializationDemo;
use PHPUnit\Framework\TestCase;

class SerializationDemoTest extends TestCase
{
    public function test_restores_serialized_properties_intact(): void
    {
        ['restored' => $restored] = SerializationDemo::run();

        $this->assertInstanceOf(RentalHistoryRecord::class, $restored);
        $this->assertSame('Canon EOS R5', $restored->equipmentName);
        $this->assertSame('Jane Doe', $restored->staffName);
        $this->assertSame('2026-08-01', $restored->checkedOutAt->format('Y-m-d'));
    }

    public function test_wakeup_replaces_excluded_property_after_restore(): void
    {
        ['restored' => $restored] = SerializationDemo::run();

        $this->assertSame('restored', $restored->internalNote());
    }

    public function test_sleep_excludes_internal_note_from_serialized_payload(): void
    {
        ['serialized' => $serialized] = SerializationDemo::run();

        $this->assertStringNotContainsString('not persisted', $serialized);
    }
}
