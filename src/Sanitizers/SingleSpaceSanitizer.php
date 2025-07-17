<?php

namespace IvanBaric\Sanigen\Sanitizers;

use IvanBaric\Sanigen\Sanitizers\Contracts\Sanitizer;

/**
 * Sanitizes a string by normalizing whitespace.
 * 
 * This sanitizer performs two operations:
 * 1. Trims leading and trailing whitespace
 * 2. Replaces any sequence of whitespace characters (spaces, tabs, newlines)
 *    with a single space
 * 
 * Useful for:
 * - Normalizing text for display or storage
 * - Cleaning user input with inconsistent spacing
 * - Ensuring consistent formatting in text fields
 * - Removing excessive whitespace from imported data
 */
final class SingleSpaceSanitizer implements Sanitizer
{
    /**
     * Normalize whitespace in the input string.
     *
     * @param string $value The input string to sanitize
     * @return string The sanitized string with normalized whitespace
     */
    public function apply(string $value): string
    {
        return preg_replace(pattern: '/\s+/', replacement: ' ', subject: trim($value)) ?: '';
    }
}
