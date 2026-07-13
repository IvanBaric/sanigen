<?php

namespace IvanBaric\Sanigen\Sanitizers;

use IvanBaric\Sanigen\Sanitizers\Contracts\Sanitizer;

/**
 * Removes HTML and PHP tags except for configured allowed tags.
 *
 * This compatibility sanitizer is not a complete XSS security boundary and must
 * not be used for content rendered through unescaped Blade output. Use
 * `safe_html` for rich HTML that will be rendered with `{!! !!}`.
 */
final class StripTagsSanitizer implements Sanitizer
{
    /**
     * Remove HTML and PHP tags from the input string, except for allowed tags.
     *
     * @param  string  $value  The input string to sanitize
     * @return string The sanitized string with only allowed HTML tags preserved
     */
    public function apply(string $value): string
    {
        $allowed = config('sanigen.allowed_html_tags', '');

        return strip_tags($value, $allowed);
    }
}
