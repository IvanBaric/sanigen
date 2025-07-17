<?php

namespace IvanBaric\Sanigen\Sanitizers;

use IvanBaric\Sanigen\Sanitizers\Contracts\Sanitizer;

/**
 * Sanitizes a string by converting it to lowercase.
 * 
 * This sanitizer uses mb_strtolower() to ensure proper handling
 * of multi-byte Unicode characters across different languages.
 */
final class LowerSanitizer implements Sanitizer
{
    /**
     * Convert the input string to lowercase.
     *
     * @param string $value The input string to convert
     * @return string The lowercase version of the input string
     */
    public function apply(string $value): string
    {
        return mb_strtolower($value);
    }
}