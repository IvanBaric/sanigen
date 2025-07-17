<?php

namespace IvanBaric\Sanigen\Sanitizers;

use IvanBaric\Sanigen\Sanitizers\Contracts\Sanitizer;

/**
 * Sanitizes a string by removing all non-letter characters.
 * 
 * This sanitizer strips any character that is not a Unicode letter
 * from the input string, leaving only alphabetic characters.
 * It supports all Unicode letter characters across different languages
 * using the \p{L} pattern in the regular expression.
 * 
 * Useful for:
 * - Ensuring text contains only alphabetic characters
 * - Removing numbers, symbols, and special characters
 * - Creating pure alphabetic identifiers
 */
final class AlphaOnlySanitizer implements Sanitizer
{
    /**
     * Remove all non-letter characters from the input string.
     *
     * @param string $value The input string to sanitize
     * @return string The sanitized string containing only letter characters
     */
    public function apply(string $value): string
    {
        return preg_replace(pattern: '/[^\p{L}]+/u', replacement: '', subject: $value) ?? '';
    }
}