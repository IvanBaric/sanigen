<?php

namespace IvanBaric\Sanigen\Sanitizers;

use IvanBaric\Sanigen\Sanitizers\Contracts\Sanitizer;

/**
 * Sanitizes a string by converting special characters to HTML entities.
 * 
 * This sanitizer uses htmlspecialchars() with HTML5 and QUOTES flags to convert
 * characters like &, ", ', <, > to their corresponding HTML entities, helping
 * prevent XSS attacks when the string is output in an HTML5 context.
 * 
 * The ENT_QUOTES flag ensures both double and single quotes are converted,
 * while ENT_HTML5 provides HTML5-specific character handling.
 * 
 * Useful for:
 * - Safely displaying user-generated content in HTML
 * - Preventing cross-site scripting (XSS) attacks
 * - Ensuring HTML5 compatibility
 */
final class HtmlSpecialCharsSanitizer implements Sanitizer
{
    /**
     * Convert special characters to HTML entities using HTML5 standards.
     *
     * @param string $value The input string to sanitize
     * @return string The sanitized string with special characters converted to HTML entities
     */
    public function apply(string $value): string
    {
        $encoding = config('sanigen.encoding', 'UTF-8');

        return htmlspecialchars($value, ENT_QUOTES | ENT_HTML5, $encoding);
    }
}
