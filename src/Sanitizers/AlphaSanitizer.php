<?php

namespace IvanBaric\Sanigen\Sanitizers;

use IvanBaric\Sanigen\Sanitizers\Contracts\Sanitizer;

final class AlphaSanitizer implements Sanitizer
{
    public function apply(string $value): string
    {
        return preg_replace('/[^\p{L}]+/u', '', $value) ?? '';
    }
}
