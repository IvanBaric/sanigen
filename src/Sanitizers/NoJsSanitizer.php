<?php

namespace IvanBaric\Sanigen\Sanitizers;

use IvanBaric\Sanigen\Sanitizers\Contracts\Sanitizer;

/**
 * Sanitizes a string to prevent JavaScript execution and Cross-Site Scripting (XSS) attacks.
 * 
 * This sanitizer implements a comprehensive approach to JavaScript removal by:
 * 1. Removing dangerous HTML tags (script, iframe, object, embed)
 * 2. Decoding HTML entities to prevent hidden malicious code
 * 3. Removing JavaScript event handlers (onclick, onload, etc.)
 * 4. Removing "javascript:" protocol from attributes
 * 5. Removing style blocks that could contain malicious code
 * 6. Removing JavaScript functions like alert(), eval(), and atob()
 * 7. Removing Base64 encoded JavaScript attacks
 * 8. Applying strip_tags to keep only allowed HTML tags
 * 9. Normalizing whitespace
 * 
 * Useful for:
 * - Sanitizing user-generated content before displaying it
 * - Preventing XSS attacks in web applications
 * - Allowing limited HTML while removing potentially dangerous elements
 * - Creating a strong security layer for text input
 */
final class NoJsSanitizer implements Sanitizer
{
    /**
     * Apply JavaScript removal protection to the input string.
     *
     * @param string $value The input string to sanitize
     * @return string The sanitized string with JavaScript and XSS vectors removed
     */
    public function apply(string $value): string
    {
        // Early return for empty values
        if ($value === '' || $value === null) {
            return '';
        }
        
        // Check if the value exceeds a reasonable length and truncate if necessary
        $maxLength = config('sanigen.max_xss_input_length', 32768); // 32KB default limit
        if (strlen($value) > $maxLength) {
            $value = substr($value, 0, $maxLength);
        }
        
        // For all other cases, proceed with normal XSS sanitization
        $encoding = config('sanigen.encoding', 'UTF-8');

        // First, convert the value to a string and decode HTML entities to handle nested encoding
        $originalValue = '';
        $iterations = 0;
        while ($originalValue !== $value && $iterations < 3) {
            $originalValue = $value;
            $value = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, $encoding);
            $iterations++;
        }

        // 1. Remove script tags and other dangerous tags more efficiently
        // First, handle complete tags with content in one pass
        $dangerousTags = ['script', 'iframe', 'object', 'embed', 'svg', 'applet', 'meta', 'base', 'style'];
        $tagPattern = implode('|', $dangerousTags);
        $value = preg_replace('/<(' . $tagPattern . ')\b[^>]*>.*?<\/\1\s*>/is', '', $value) ?? $value;
        
        // Then, remove unclosed tags in one pass
        $value = preg_replace('/<(' . $tagPattern . ')\b[^>]*>/is', '', $value) ?? $value;

        // 2. Remove all event handlers (onclick, onload, etc.) - simplified pattern
        $value = preg_replace('/\s+on\w+\s*=\s*["\'][^"\']*["\']|\s+on\w+\s*=/si', '', $value) ?? $value;
        
        // 3. Remove javascript: protocol from attributes (href, src, etc.) - simplified pattern
        $value = preg_replace('/\s+(?:href|src|action|data)\s*=\s*["\']?\s*(?:j\s*a\s*v\s*a\s*s\s*c\s*r\s*i\s*p\s*t\s*:|&#x?[0-9a-f]+;?j)[^>]*/i', '', $value) ?? $value;
        
        // 4. Remove data URIs that could contain scripts - simplified pattern
        $value = preg_replace('/data\s*:\s*\w+\/[^;]+;\s*base64\s*,[a-zA-Z0-9+\/=\s]+/i', '', $value) ?? $value;

        // 5. Remove dangerous HTML elements with event handlers or javascript: URLs
        // This combines the previous steps 7 and 8 into a more efficient pattern
        $value = preg_replace('/<a\b[^>]*href\s*=\s*["\']?\s*javascript:[^>]*>/i', '', $value) ?? $value;
        $value = preg_replace('/<img\b[^>]*(?:onerror|onload|onmouseover|onclick)\s*=[^>]*>/i', '', $value) ?? $value;
        
        // 6. Remove JavaScript alert() functions - simplified pattern
        $value = preg_replace('/alert\s*\([^)]*\)/is', '', $value) ?? $value;
        
        // 7. Remove JavaScript eval() function calls - simplified pattern
        $value = preg_replace('/eval\s*\([^)]*\)/is', '', $value) ?? $value;
        
        // 8. Remove JavaScript atob() function calls - simplified pattern
        $value = preg_replace('/atob\s*\([^)]*\)/is', '', $value) ?? $value;
        
        // 9. Keep only allowed HTML tags from configuration
        $allowed = config('sanigen.allowed_html_tags', '');
        $value = strip_tags($value, $allowed);

        // 10. Normalize whitespace
        return preg_replace('/\s+/', ' ', trim($value)) ?: '';
    }
}