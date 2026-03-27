<?php

namespace IvanBaric\Sanigen\Sanitizers;

use IvanBaric\Sanigen\Sanitizers\Contracts\Sanitizer;

/**
 * Sanitizes a string to be a valid URL with a protocol.
 * 
 * This sanitizer ensures that URLs have a protocol prefix by:
 * 1. Checking if the URL already starts with "http://" or "https://"
 * 2. If not, adding "https://" to the beginning and removing any leading slashes
 * 
 * The result is a URL that always has a protocol, defaulting to HTTPS
 * for URLs that didn't specify one.
 * 
 * Useful for:
 * - Ensuring URLs are complete and properly formatted
 * - Enforcing HTTPS by default for security
 * - Normalizing user-entered URLs
 */
final class UrlSanitizer implements Sanitizer
{
    /**
     * Sanitize a string to be a valid URL with a protocol.
     *
     * @param string $value The input string to sanitize
     * @return string The sanitized URL with a protocol
     */
    public function apply(string $value): string
    {
        $value = trim($value);

        if ($value === '') {
            return '';
        }

        // Preserve existing safe schemes and reject explicitly dangerous ones.
        if (preg_match('/^[a-z][a-z0-9+\-.]*:/i', $value)) {
            if (preg_match('/^https?:\/\//i', $value)) {
                return $value;
            }

            if (preg_match('/^(?:javascript|data|vbscript):/i', $value)) {
                return '';
            }

            return $value;
        }

        if (str_starts_with($value, '//')) {
            return 'https:' . $value;
        }

        // Force HTTPS protocol if no protocol is specified
        return 'https://' . ltrim($value, '/');
    }
}
