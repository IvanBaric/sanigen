<?php

namespace IvanBaric\Sanigen\Sanitizers;

use IvanBaric\Sanigen\Sanitizers\Contracts\Sanitizer;

/**
 * Sanitizes a string by removing HTML and PHP tags, except for allowed tags.
 * 
 * This sanitizer uses PHP's strip_tags() function to remove HTML and PHP tags
 * from the input string, but allows specific tags to be preserved based on
 * configuration. The allowed tags are defined in the 'sanigen.allowed_html_tags'
 * configuration setting.
 * 
 * Useful for:
 * - Allowing limited HTML formatting while removing potentially dangerous tags
 * - Creating a controlled whitelist of acceptable HTML elements
 * - Sanitizing user input for display in HTML contexts with basic formatting
 * - Implementing a restricted markup system
 */
final class StripTagsSanitizer implements Sanitizer
{
    /**
     * Remove HTML and PHP tags from the input string, except for allowed tags.
     *
     * @param string $value The input string to sanitize
     * @return string The sanitized string with only allowed HTML tags preserved
     */
    public function apply(string $value): string
    {
        $allowed = config('sanigen.allowed_html_tags', '');
        return strip_tags($value, $allowed);
    }
}
