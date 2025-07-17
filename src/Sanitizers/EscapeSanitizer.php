<?php

namespace IvanBaric\Sanigen\Sanitizers;

use IvanBaric\Sanigen\Sanitizers\Contracts\Sanitizer;

/**
 * Sanitizes a string by double-escaping special characters to HTML entities.
 * 
 * This sanitizer applies htmlspecialchars() twice to convert characters like &, ", ', <, >
 * to their corresponding HTML entities, and then escapes those entities again.
 * For example, '<' becomes '&lt;' and then '&amp;lt;'.
 * 
 * This double-escaping is useful for scenarios where the content might be processed
 * by another HTML parser after initial escaping, helping prevent XSS attacks in
 * complex rendering pipelines.
 * 
 * The sanitizer uses both ENT_QUOTES (converts both double and single quotes)
 * and ENT_SUBSTITUTE (replaces invalid code sequences with a replacement character)
 * flags for maximum safety.
 */
final class EscapeSanitizer implements Sanitizer
{
    /**
     * Convert special characters to HTML entities.
     *
     * @param string $value The input string to sanitize
     * @return string The sanitized string with special characters converted to HTML entities
     */
    public function apply(string $value): string
    {
        $encoding = config('sanigen.encoding', 'UTF-8');
        // Apply htmlspecialchars twice to double-escape HTML entities
        $escaped = htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, $encoding);
        return htmlspecialchars($escaped, ENT_QUOTES | ENT_SUBSTITUTE, $encoding);
    }
}
