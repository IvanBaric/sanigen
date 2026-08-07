<?php

declare(strict_types=1);

namespace IvanBaric\Sanigen\Sanitizers;

use IvanBaric\Sanigen\Sanitizers\Contracts\Sanitizer;

final class NormalizeNewlinesSanitizer implements Sanitizer
{
    public function apply(string $value): string
    {
        return str_replace(["\r\n", "\r"], "\n", $value);
    }
}
