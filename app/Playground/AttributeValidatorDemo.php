<?php

namespace App\Playground;

use App\Attributes\Validate\MaxLength;

class AttributeValidatorDemo
{
    /** @return string[] validation errors, empty if valid */
    public static function validate(object $target): array
    {
        $errors = [];
        $reflection = new \ReflectionObject($target);

        foreach ($reflection->getProperties() as $property) {
            foreach ($property->getAttributes(MaxLength::class) as $attribute) {
                if (! $property->isInitialized($target)) {
                    continue;
                }

                /** @var MaxLength $instance */
                $instance = $attribute->newInstance();
                $value = $property->getValue($target);

                if (is_string($value) && strlen($value) > $instance->limit) {
                    $errors[] = "{$property->getName()} exceeds max length {$instance->limit}.";
                }
            }
        }

        return $errors;
    }
}
