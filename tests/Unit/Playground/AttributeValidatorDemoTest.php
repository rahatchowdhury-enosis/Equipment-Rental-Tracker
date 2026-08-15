<?php

namespace Tests\Unit\Playground;

use App\Attributes\Validate\MaxLength;
use App\Playground\AttributeValidatorDemo;
use App\Playground\DemoDto;
use PHPUnit\Framework\TestCase;

class AttributeValidatorDemoTest extends TestCase
{
    public function test_returns_empty_array_when_properties_within_limit(): void
    {
        $dto = new DemoDto;
        $dto->title = 'Short title';
        $dto->code = 'ABC';

        $this->assertSame([], AttributeValidatorDemo::validate($dto));
    }

    public function test_returns_error_when_property_exceeds_max_length(): void
    {
        $dto = new DemoDto;
        $dto->title = 'Short title';
        $dto->code = str_repeat('X', 31);

        $errors = AttributeValidatorDemo::validate($dto);

        $this->assertCount(1, $errors);
        $this->assertStringContainsString('code', $errors[0]);
        $this->assertStringContainsString('30', $errors[0]);
    }

    public function test_ignores_properties_without_max_length_attribute(): void
    {
        $target = new class
        {
            public string $unannotated = '';
        };
        $target->unannotated = str_repeat('Y', 1000);

        $this->assertSame([], AttributeValidatorDemo::validate($target));
    }

    public function test_skips_non_string_property_silently(): void
    {
        $target = new class
        {
            #[MaxLength(2)]
            public int $qty = 999999;
        };

        $this->assertSame([], AttributeValidatorDemo::validate($target));
    }

    public function test_skips_uninitialized_typed_property_without_throwing(): void
    {
        $target = new class
        {
            #[MaxLength(5)]
            public string $noDefault;
        };

        $this->assertSame([], AttributeValidatorDemo::validate($target));
    }
}
