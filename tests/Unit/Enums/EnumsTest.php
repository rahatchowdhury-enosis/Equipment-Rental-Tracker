<?php

namespace Tests\Unit\Enums;

use App\Enums\Condition;
use App\Enums\EquipmentStatus;
use App\Enums\RentalStatus;
use App\Enums\Role;
use PHPUnit\Framework\TestCase;
use ValueError;

class EnumsTest extends TestCase
{
    public function test_equipment_status_from_valid_value(): void
    {
        $this->assertSame(EquipmentStatus::Available, EquipmentStatus::from('available'));
        $this->assertSame(EquipmentStatus::CheckedOut, EquipmentStatus::from('checked_out'));
    }

    public function test_equipment_status_from_invalid_value_throws(): void
    {
        $this->expectException(ValueError::class);
        EquipmentStatus::from('bogus');
    }

    public function test_equipment_status_label(): void
    {
        $this->assertSame('Available', EquipmentStatus::Available->label());
        $this->assertSame('Checked Out', EquipmentStatus::CheckedOut->label());
    }

    public function test_rental_status_from_valid_value(): void
    {
        $this->assertSame(RentalStatus::Active, RentalStatus::from('active'));
        $this->assertSame(RentalStatus::Returned, RentalStatus::from('returned'));
    }

    public function test_rental_status_from_invalid_value_throws(): void
    {
        $this->expectException(ValueError::class);
        RentalStatus::from('bogus');
    }

    public function test_rental_status_label(): void
    {
        $this->assertSame('Active', RentalStatus::Active->label());
        $this->assertSame('Returned', RentalStatus::Returned->label());
    }

    public function test_role_from_valid_value(): void
    {
        $this->assertSame(Role::Admin, Role::from('admin'));
        $this->assertSame(Role::Staff, Role::from('staff'));
    }

    public function test_role_from_invalid_value_throws(): void
    {
        $this->expectException(ValueError::class);
        Role::from('bogus');
    }

    public function test_role_label(): void
    {
        $this->assertSame('Admin', Role::Admin->label());
        $this->assertSame('Staff', Role::Staff->label());
    }

    public function test_condition_from_valid_value(): void
    {
        $this->assertSame(Condition::Good, Condition::from('good'));
        $this->assertSame(Condition::Damaged, Condition::from('damaged'));
        $this->assertSame(Condition::Lost, Condition::from('lost'));
    }

    public function test_condition_from_invalid_value_throws(): void
    {
        $this->expectException(ValueError::class);
        Condition::from('bogus');
    }

    public function test_condition_label(): void
    {
        $this->assertSame('Good', Condition::Good->label());
        $this->assertSame('Damaged', Condition::Damaged->label());
        $this->assertSame('Lost', Condition::Lost->label());
    }
}
