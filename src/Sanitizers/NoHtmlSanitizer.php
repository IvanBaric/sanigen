<?php

namespace IvanBaric\Sanigen\Sanitizers;

use IvanBaric\Sanigen\Sanitizers\Contracts\Sanitizer;

/**
 * Sanitizes a string by removing all HTML and PHP tags.
 * 
 * This sanitizer uses PHP's strip_tags() function to completely remove
 * all HTML and PHP tags from the input string, leaving only the text content.
 * Unlike htmlspecialchars(), which encodes tags, this sanitizer removes them entirely.
 * 
 * Useful for:
 * - Ensuring plain text output with no markup
 * - Preventing HTML injection attacks
 * - Extracting text content from HTML-formatted input
 * - Storing data that should never contain HTML
 */
final class NoHtmlSanitizer implements Sanitizer
{
    /**
     * Remove all HTML and PHP tags from the input string.
     *
     * @param string $value The input string to sanitize
     * @return string The sanitized string with all HTML and PHP tags removed
     */
    public function apply(string $value): string
    {
        return strip_tags($value);
    }
}
