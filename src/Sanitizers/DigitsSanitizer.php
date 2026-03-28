<?php

namespace IvanBaric\Sanigen\Sanitizers;

use IvanBaric\Sanigen\Sanitizers\Contracts\Sanitizer;

final class DigitsSanitizer implements Sanitizer
{
    public function apply(string $value): string
    {
        return preg_replace('/[^\d]/', '', $value) ?: '';
    }
}
