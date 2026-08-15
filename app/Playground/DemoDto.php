<?php

namespace App\Playground;

use App\Attributes\Validate\MaxLength;

class DemoDto
{
    #[MaxLength(120)]
    public string $title = '';

    #[MaxLength(30)]
    public string $code = '';
}
