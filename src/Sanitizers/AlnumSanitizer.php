<?php

namespace IvanBaric\Sanigen\Sanitizers;

use IvanBaric\Sanigen\Sanitizers\Contracts\Sanitizer;

final class AlnumSanitizer implements Sanitizer
{
    public function apply(string $value): string
    {
        return preg_replace('/[^\p{L}\p{N}]+/u', '', $value) ?? '';
    }
}
