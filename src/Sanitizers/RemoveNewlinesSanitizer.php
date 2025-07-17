<?php

namespace IvanBaric\Sanigen\Sanitizers;

use IvanBaric\Sanigen\Sanitizers\Contracts\Sanitizer;

/**
 * Sanitizes a string by removing all newline characters.
 * 
 * This sanitizer removes both carriage returns (\r) and newlines (\n)
 * from the input string, converting multi-line text to a single line.
 * 
 * Useful for:
 * - Ensuring text remains on a single line
 * - Preparing data for CSV files or other formats that require single-line entries
 * - Removing line breaks from user input for specific display contexts
 * - Normalizing text for processing where newlines would cause issues
 */
final class RemoveNewlinesSanitizer implements Sanitizer
{
    /**
     * Remove all newline characters from the input string.
     *
     * @param string $value The input string to sanitize
     * @return string The sanitized string with all newlines removed
     */
    public function apply(string $value): string
    {
        return str_replace(search: ["\r", "\n"], replace: '', subject: $value);
    }
}
