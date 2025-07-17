<?php

namespace IvanBaric\Sanigen\Sanitizers;

use IvanBaric\Sanigen\Sanitizers\Contracts\Sanitizer;

/**
 * Sanitizes a string by capitalizing its first character.
 * 
 * This sanitizer uses PHP's ucfirst() function to convert the first
 * character of the input string to uppercase, leaving the rest unchanged.
 * 
 * Useful for:
 * - Ensuring proper capitalization of titles, names, or sentences
 * - Standardizing text formatting for display
 * - Correcting user input with improper capitalization
 * - Formatting database values for presentation
 */
final class UcfirstSanitizer implements Sanitizer
{
    /**
     * Capitalize the first character of the input string.
     *
     * @param string $value The input string to sanitize
     * @return string The sanitized string with its first character capitalized
     */
    public function apply(string $value): string
    {
        return ucfirst($value);
    }
}
