<?php

namespace IvanBaric\Sanigen\Sanitizers;

use IvanBaric\Sanigen\Sanitizers\Contracts\Sanitizer;

/**
 * Sanitizes a string by removing all non-alphanumeric characters.
 * 
 * This sanitizer strips any character that is not a Unicode letter or number
 * from the input string, leaving only alphanumeric characters.
 * It supports all Unicode letter and number characters across different languages
 * using the \p{L} and \p{N} patterns in the regular expression.
 * 
 * Useful for:
 * - Creating clean identifiers or usernames
 * - Removing symbols, punctuation, and special characters
 * - Ensuring text contains only letters and numbers
 */
final class AlphanumericOnlySanitizer implements Sanitizer
{
    /**
     * Remove all non-alphanumeric characters from the input string.
     *
     * @param string $value The input string to sanitize
     * @return string The sanitized string containing only letters and numbers
     */
    public function apply(string $value): string
    {
        return preg_replace(pattern: '/[^\p{L}\p{N}]+/u', replacement: '', subject: $value) ?? '';
    }
}