<?php

namespace IvanBaric\Sanigen\Sanitizers;

use IvanBaric\Sanigen\Sanitizers\Contracts\Sanitizer;

/**
 * Sanitizes a string by removing all non-ASCII characters.
 * 
 * This sanitizer strips any character that is outside the standard ASCII range
 * (0x00-0x7F, or decimal 0-127) from the input string. This includes all
 * extended characters, accented letters, non-Latin scripts, emojis, and
 * other Unicode characters.
 * 
 * Useful for:
 * - Ensuring compatibility with systems that only support ASCII
 * - Removing special characters and diacritics
 * - Simplifying text for technical contexts where Unicode might cause issues
 */
final class AsciiOnlySanitizer implements Sanitizer
{
    /**
     * Remove all non-ASCII characters from the input string.
     *
     * @param string $value The input string to sanitize
     * @return string The sanitized string containing only ASCII characters (0x00-0x7F)
     */
    public function apply(string $value): string
    {
        return preg_replace(pattern: '/[^\x00-\x7F]+/', replacement: '', subject: $value) ?? '';
    }
}