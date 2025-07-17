<?php

namespace IvanBaric\Sanigen\Sanitizers;

use IvanBaric\Sanigen\Sanitizers\Contracts\Sanitizer;

/**
 * Sanitizes a string by removing all emoji characters.
 * 
 * This sanitizer uses a regular expression to match and remove various
 * Unicode emoji blocks, including emoticons, symbols, pictographs,
 * transport symbols, flags, and other special characters.
 * 
 * Useful for:
 * - Ensuring text compatibility with systems that don't support emojis
 * - Cleaning user input for storage in databases with limited Unicode support
 * - Standardizing text for processing or analysis
 * - Preventing display issues in environments with limited font support
 */
final class EmojiRemoveSanitizer implements Sanitizer
{
    /**
     * Remove all emoji characters from the input string.
     *
     * @param string $value The input string to sanitize
     * @return string The sanitized string with all emoji characters removed
     */
    public function apply(string $value): string
    {
        // Remove common Unicode emoji blocks
        return preg_replace(
            '/[\x{1F600}-\x{1F64F}]|' . // Emoticons
            '[\x{1F300}-\x{1F5FF}]|' . // Symbols & pictographs
            '[\x{1F680}-\x{1F6FF}]|' . // Transport & map symbols
            '[\x{2600}-\x{26FF}]|'   . // Misc symbols
            '[\x{2700}-\x{27BF}]|'   . // Dingbats
            '[\x{1F900}-\x{1F9FF}]|' . // Supplemental Symbols and Pictographs
            '[\x{1FA70}-\x{1FAFF}]|' . // Symbols and Pictographs Extended-A
            '[\x{1F1E6}-\x{1F1FF}]'  . // Regional indicator symbols (flags)
            '/u',
            '',
            $value
        ) ?? $value;
    }
}
