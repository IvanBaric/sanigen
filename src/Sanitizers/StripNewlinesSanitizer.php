<?php

namespace IvanBaric\Sanigen\Sanitizers;

use IvanBaric\Sanigen\Sanitizers\Contracts\Sanitizer;

final class StripNewlinesSanitizer implements Sanitizer
{
    public function apply(string $value): string
    {
        return str_replace(["\r", "\n"], '', $value);
    }
}
