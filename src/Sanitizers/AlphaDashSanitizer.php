<?php

namespace IvanBaric\Sanigen\Sanitizers;

use IvanBaric\Sanigen\Sanitizers\Contracts\Sanitizer;

/**
 * Sanitizes a string by allowing only letters, numbers, hyphens, and underscores.
 * 
 * This sanitizer strips any character that is not a Unicode letter, number,
 * hyphen (-), or underscore (_) from the input string. It supports all Unicode
 * letter and number characters across different languages using the \p{L} and
 * \p{N} patterns in the regular expression.
 * 
 * Useful for:
 * - Creating URL-friendly slugs or identifiers
 * - Sanitizing input for database column names
 * - Ensuring text follows common naming conventions for variables or files
 */
final class AlphaDashSanitizer implements Sanitizer
{
    /**
     * Remove all characters except letters, numbers, hyphens, and underscores.
     *
     * @param string $value The input string to sanitize
     * @return string The sanitized string containing only allowed characters
     */
    public function apply(string $value): string
    {
        return preg_replace(pattern: '/[^\p{L}\p{N}\-_]+/u', replacement: '', subject: $value) ?? '';
    }
}