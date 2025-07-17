<?php

namespace IvanBaric\Sanigen\Sanitizers;

use IvanBaric\Sanigen\Sanitizers\Contracts\Sanitizer;

/**
 * Sanitizes a string by converting it to uppercase.
 * 
 * This sanitizer uses mb_strtoupper() to ensure proper handling
 * of multi-byte Unicode characters across different languages.
 * 
 * Useful for:
 * - Standardizing text for display in headings or labels
 * - Creating consistent formatting for acronyms or codes
 * - Emphasizing text in specific UI contexts
 * - Normalizing input for case-insensitive comparisons
 */
final class UpperSanitizer implements Sanitizer
{
    /**
     * Convert the input string to uppercase.
     *
     * @param string $value The input string to convert
     * @return string The uppercase version of the input string
     */
    public function apply(string $value): string
    {
        return mb_strtoupper($value);
    }
}
