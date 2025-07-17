<?php

namespace IvanBaric\Sanigen\Sanitizers;

use IvanBaric\Sanigen\Sanitizers\Contracts\Sanitizer;

/**
 * Sanitizes a string by converting it to a URL-friendly slug.
 * 
 * This sanitizer creates a slug by:
 * 1. Converting the string to lowercase
 * 2. Trimming leading and trailing whitespace
 * 3. Replacing any sequence of characters that are not letters or numbers with hyphens
 * 4. Removing any leading or trailing hyphens
 * 
 * Note: This implementation uses a custom approach rather than Laravel's Str::slug() helper,
 * though the Str class is imported but not used.
 * 
 * Useful for:
 * - Creating URL-friendly versions of titles or names
 * - Generating file names from user input
 * - Creating readable identifiers for database records
 * - Ensuring consistent formatting for route parameters
 */
final class SlugSanitizer implements Sanitizer
{
    /**
     * Convert the input string to a URL-friendly slug.
     *
     * @param string $value The input string to sanitize
     * @return string The sanitized string as a URL-friendly slug
     */
    public function apply(string $value): string
    {
        // Create a simple slug: lowercase, trim, replace spaces and unwanted characters
        $slug = mb_strtolower(trim($value));
        $slug = preg_replace('/[^\p{L}\p{N}]+/u', '-', $slug) ?: '';
        return trim($slug, '-');
    }
}
