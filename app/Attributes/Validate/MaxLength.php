<?php

namespace App\Attributes\Validate;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class MaxLength
{
    public function __construct(public int $limit) {}
}
