<?php

declare(strict_types=1);

namespace Tests\Fixtures;

use IvanBaric\Sanigen\Sanitizers\Contracts\Sanitizer;

final class CountingSanitizer implements Sanitizer
{
    public static int $calls = 0;

    public function apply(string $value): string
    {
        self::$calls++;

        return trim($value);
    }
}
