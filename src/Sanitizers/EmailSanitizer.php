<?php

namespace IvanBaric\Sanigen\Sanitizers;

use IvanBaric\Sanigen\Sanitizers\Contracts\Sanitizer;

/**
 * Sanitizes a string to be a valid email address.
 * 
 * This sanitizer performs multiple operations to clean an email address:
 * 1. Converts the email to lowercase
 * 2. Trims leading and trailing whitespace
 * 3. Removes control characters (invisible characters)
 * 4. Applies PHP's FILTER_SANITIZE_EMAIL with Unicode support
 * 
 * The result is a normalized email address with potentially invalid
 * characters removed. Note that this sanitizer does not validate
 * whether the email is actually valid - it only cleans the input.
 */
final class EmailSanitizer implements Sanitizer
{
    /**
     * Sanitize a string to be a valid email address.
     *
     * @param string $value The input string to sanitize
     * @return string The sanitized email address
     */
    public function apply(string $value): string
    {
        // Convert to lowercase and trim whitespace
        $clean = mb_strtolower(trim($value));
        
        // Remove control characters (invisible characters)
        $clean = preg_replace(pattern: '/[\p{C}]/u', replacement: '', subject: $clean);
        
        // Apply PHP's email sanitization filter with Unicode support
        return filter_var(value: $clean, filter: FILTER_SANITIZE_EMAIL, options: FILTER_FLAG_EMAIL_UNICODE) ?: '';
    }
}
