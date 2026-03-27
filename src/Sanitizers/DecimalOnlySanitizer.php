<?php

namespace IvanBaric\Sanigen\Sanitizers;

use IvanBaric\Sanigen\Sanitizers\Contracts\Sanitizer;

/**
 * Sanitizes a string to contain only valid decimal number characters.
 * 
 * This sanitizer performs two operations:
 * 1. Normalizes decimal separators by replacing commas with periods
 * 2. Removes all characters except digits and periods
 * 
 * The result is a string that can be safely converted to a decimal number.
 * 
 * Useful for:
 * - Cleaning user input for decimal number fields
 * - Normalizing number formats from different locales
 * - Preparing strings for numeric operations
 */
final class DecimalOnlySanitizer implements Sanitizer
{
    /**
     * Convert a string to contain only valid decimal number characters.
     *
     * @param string $value The input string to sanitize
     * @return string The sanitized string containing only digits and decimal points
     */
    public function apply(string $value): string
    {
        // Normalize decimal separators
        $normalized = str_replace(',', '.', $value);

        // Keep only digits and decimal points
        $clean = preg_replace(pattern: '/[^0-9\.]+/', replacement: '', subject: $normalized) ?? '';

        // If the input contained thousand separators (e.g. 1,234.56 -> 1.234.56),
        // collapse all dots except the last one, treating the last dot as the decimal separator.
        $parts = explode('.', $clean);
        if (count($parts) <= 2) {
            return $clean;
        }

        $decimal = array_pop($parts);
        $integer = implode('', $parts);

        if ($decimal === '') {
            return $integer;
        }

        return $integer . '.' . $decimal;
    }
}
