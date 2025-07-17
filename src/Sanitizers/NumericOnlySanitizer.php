<?php

namespace IvanBaric\Sanigen\Sanitizers;

use IvanBaric\Sanigen\Sanitizers\Contracts\Sanitizer;

/**
 * Sanitizes a string by removing all non-digit characters.
 * 
 * This sanitizer strips any character that is not a numeric digit (0-9)
 * from the input string, leaving only the numeric digits.
 * 
 * Useful for:
 * - Cleaning user input for numeric-only fields
 * - Extracting digits from formatted numbers (e.g., "1,234.56" becomes "123456")
 * - Preparing strings for numeric operations
 */
final class NumericOnlySanitizer implements Sanitizer
{
    /**
     * Remove all non-digit characters from the input string.
     *
     * @param string $value The input string to sanitize
     * @return string The sanitized string containing only numeric digits (0-9)
     */
    public function apply(string $value): string
    {
        return preg_replace(pattern: '/[^\d]/', replacement: '', subject: $value) ?: '';
    }
}
