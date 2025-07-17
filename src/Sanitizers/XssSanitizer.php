<?php

namespace IvanBaric\Sanigen\Sanitizers;

use IvanBaric\Sanigen\Sanitizers\Contracts\Sanitizer;

/**
 * Sanitizes a string to prevent Cross-Site Scripting (XSS) attacks.
 * 
 * This sanitizer implements a comprehensive approach to XSS prevention by:
 * 1. Removing dangerous HTML tags (script, iframe, object, embed)
 * 2. Decoding HTML entities to prevent hidden malicious code
 * 3. Removing JavaScript event handlers (onclick, onload, etc.)
 * 4. Removing "javascript:" protocol from attributes
 * 5. Removing style blocks that could contain malicious code
 * 6. Applying strip_tags to keep only allowed HTML tags
 * 7. Normalizing whitespace
 * 
 * Useful for:
 * - Sanitizing user-generated content before displaying it
 * - Preventing XSS attacks in web applications
 * - Allowing limited HTML while removing potentially dangerous elements
 * - Creating a strong security layer for text input
 */
final class XssSanitizer implements Sanitizer
{
    /**
     * Apply XSS protection to the input string.
     *
     * @param string $value The input string to sanitize
     * @return string The sanitized string with XSS vectors removed
     */
    public function apply(string $value): string
    {
        $encoding = config('sanigen.encoding', 'UTF-8');

        // 1. Remove <script>, <iframe>, <object>, <embed> tags and their content
        $value = preg_replace('#<(script|iframe|object|embed)[^>]*>.*?</\1>#is', '', $value) ?? $value;

        // 2. Decode HTML entities to prevent hidden malicious code
        $value = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, $encoding);

        // 3. Remove JavaScript event handlers (onclick, onload, etc.)
        $value = preg_replace('/on\w+\s*=\s*(["\']).*?\1/si', '', $value) ?? $value;

        // 4. Remove "javascript:" protocol from href, src, style attributes
        $value = preg_replace('/javascript\s*:/i', '', $value) ?? $value;

        // 5. Remove <style> blocks that could contain malicious code
        $value = preg_replace('#<style[^>]*>.*?</style>#is', '', $value) ?? $value;

        // 6. Finally, keep only allowed HTML tags from configuration
        $allowed = config('sanigen.allowed_html_tags', '');
        $value = strip_tags($value, $allowed);

        // 7. Normalize whitespace (remove multiple spaces)
        return preg_replace('/\s+/', ' ', trim($value)) ?: '';
    }
}

