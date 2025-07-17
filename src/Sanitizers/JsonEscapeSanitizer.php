<?php

namespace IvanBaric\Sanigen\Sanitizers;

use IvanBaric\Sanigen\Sanitizers\Contracts\Sanitizer;

/**
 * Sanitizes a string by escaping special characters for JSON encoding.
 * 
 * This sanitizer replaces backslashes, double quotes, newlines, carriage returns,
 * and tabs with their escaped equivalents for safe inclusion in JSON strings.
 * 
 * Useful for:
 * - Preparing text for manual JSON construction
 * - Ensuring string values are properly escaped for JSON contexts
 * - Preventing JSON syntax errors when including user input in JSON data
 * - Pre-processing text before using it in JavaScript code
 */
final class JsonEscapeSanitizer implements Sanitizer
{
    /**
     * Escape special characters for JSON encoding.
     *
     * @param string $value The input string to sanitize
     * @return string The sanitized string with JSON special characters escaped
     */
    public function apply(string $value): string
    {
        return str_replace(
            ['\\', '"', "\n", "\r", "\t"],
            ['\\\\', '\\"', '\\n', '\\r', '\\t'],
            $value
        );
    }
}

