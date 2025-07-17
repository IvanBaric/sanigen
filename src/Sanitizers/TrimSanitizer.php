<?php

namespace IvanBaric\Sanigen\Sanitizers;

use IvanBaric\Sanigen\Sanitizers\Contracts\Sanitizer;

/**
 * Sanitizes a string by normalizing whitespace and trimming.
 * 
 * This sanitizer performs three operations:
 * 1. Converts various Unicode whitespace characters to standard ASCII spaces
 * 2. Removes ASCII control characters
 * 3. Trims leading and trailing whitespace
 */
final class TrimSanitizer implements Sanitizer
{
    /**
     * Apply the trim sanitization to a string value.
     *
     * @param string $value The input string to sanitize
     * @return string The sanitized string
     */
    public function apply(string $value): string
    {
        // Normalize Unicode whitespace characters to standard ASCII spaces
        $value = preg_replace(
            pattern: '/[\x{00A0}\x{1680}\x{180E}\x{2000}-\x{200D}\x{202F}\x{205F}\x{3000}\x{FEFF}]/u',
            replacement: ' ',
            subject: $value
        ) ?? $value;
        
        // Remove ASCII control characters (0-31 and DEL/127)
        $value = preg_replace(
            pattern: '/[\x00-\x1F\x7F]/u', 
            replacement: '', 
            subject: $value
        ) ?? $value;
        
        // Trim leading and trailing whitespace
        return trim($value);
    }
}
