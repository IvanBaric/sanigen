<?php

namespace IvanBaric\Sanigen\Sanitizers;

use IvanBaric\Sanigen\Sanitizers\Contracts\Sanitizer;

final class StripHtmlSanitizer implements Sanitizer
{
    public function apply(string $value): string
    {
        return strip_tags($value);
    }
}
