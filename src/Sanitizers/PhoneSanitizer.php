<?php

namespace IvanBaric\Sanigen\Sanitizers;

use IvanBaric\Sanigen\Sanitizers\Contracts\Sanitizer;

/**
 * Sanitizes a string to be a valid phone number.
 * 
 * This sanitizer normalizes phone numbers by:
 * 1. Trimming whitespace
 * 2. Removing all characters except digits and the plus sign
 * 3. Handling multiple plus signs by keeping only one at the beginning
 * 
 * The result is a clean phone number that contains only digits and
 * at most one plus sign at the beginning (for international format).
 * 
 * Useful for:
 * - Normalizing phone numbers from user input
 * - Removing formatting characters (spaces, parentheses, dashes)
 * - Ensuring consistent phone number format for storage or processing
 */
final class PhoneSanitizer implements Sanitizer
{
    /**
     * Sanitize a string to be a valid phone number.
     *
     * @param string $value The input string to sanitize
     * @return string The sanitized phone number
     */
    public function apply(string $value): string
    {
        // Remove all characters except digits and plus signs
        $clean = preg_replace(pattern: '/[^\d+]/', replacement: '', subject: trim($value)) ?: '';
        
        // If there are multiple plus signs, keep only one at the beginning
        if (substr_count($clean, '+') > 1) {
            $clean = '+' . preg_replace(pattern: '/\D+/', replacement: '', subject: $clean);
        }
        
        return $clean;
    }
}
