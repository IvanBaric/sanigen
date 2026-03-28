<?php

namespace IvanBaric\Sanigen\Sanitizers;

use IvanBaric\Sanigen\Sanitizers\Contracts\Sanitizer;

final class AsciiSanitizer implements Sanitizer
{
    public function apply(string $value): string
    {
        return preg_replace('/[^\x00-\x7F]+/', '', $value) ?? '';
    }
}
