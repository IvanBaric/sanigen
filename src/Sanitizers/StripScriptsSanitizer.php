<?php

namespace IvanBaric\Sanigen\Sanitizers;

use IvanBaric\Sanigen\Sanitizers\Contracts\Sanitizer;

final class StripScriptsSanitizer implements Sanitizer
{
    /**
     * Removes common script-bearing markup, inline JS handlers, dangerous protocols, and suspicious JS patterns from text input.
     */
    public function apply(string $value): string
    {
        if ($value === '') {
            return '';
        }

        $maxLength = (int) config('sanigen.max_strip_scripts_input_length', 32768);
        if (strlen($value) > $maxLength) {
            $value = substr($value, 0, $maxLength);
        }

        $encoding = config('sanigen.encoding', 'UTF-8');

        $originalValue = '';
        $iterations = 0;
        while ($originalValue !== $value && $iterations < 3) {
            $originalValue = $value;
            $value = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, $encoding);
            $iterations++;
        }

        $dangerousTags = ['script', 'iframe', 'object', 'embed', 'svg', 'applet', 'meta', 'base', 'style'];
        $tagPattern = implode('|', $dangerousTags);
        $value = preg_replace('/<('.$tagPattern.')\b[^>]*>.*?<\/\1\s*>/is', '', $value) ?? $value;
        $value = preg_replace('/<('.$tagPattern.')\b[^>]*>/is', '', $value) ?? $value;

        $value = preg_replace('/\s+on\w+\s*=\s*["\'][^"\']*["\']|\s+on\w+\s*=/si', '', $value) ?? $value;
        $value = preg_replace('/\s+(?:href|src|action|data)\s*=\s*["\']?\s*(?:j\s*a\s*v\s*a\s*s\s*c\s*r\s*i\s*p\s*t\s*:|&#x?[0-9a-f]+;?j)[^>]*/i', '', $value) ?? $value;
        $value = preg_replace('/data\s*:\s*\w+\/[^;]+;\s*base64\s*,[a-zA-Z0-9+\/=\s]+/i', '', $value) ?? $value;

        $value = preg_replace('/<a\b[^>]*href\s*=\s*["\']?\s*javascript:[^>]*>/i', '', $value) ?? $value;
        $value = preg_replace('/<img\b[^>]*(?:onerror|onload|onmouseover|onclick)\s*=[^>]*>/i', '', $value) ?? $value;

        $value = preg_replace('/alert\s*\([^)]*\)/is', '', $value) ?? $value;
        $value = preg_replace('/eval\s*\([^)]*\)/is', '', $value) ?? $value;
        $value = preg_replace('/atob\s*\([^)]*\)/is', '', $value) ?? $value;
        $value = preg_replace('/\b(?:fetch|setTimeout|setInterval|XMLHttpRequest|importScripts)\s*\([^)]*\)/is', '', $value) ?? $value;
        $value = preg_replace('/\bnew\s+Function\s*\([^)]*\)/is', '', $value) ?? $value;
        $value = preg_replace('/\bFunction\s*\([^)]*\)/is', '', $value) ?? $value;
        $value = preg_replace('/\b(?:document\.cookie|window\.location|location\.href)\s*=\s*[^;]+;?/is', '', $value) ?? $value;
        $value = preg_replace('/\bdocument\.cookie\b/is', '', $value) ?? $value;

        $allowed = config('sanigen.allowed_html_tags', '');
        $value = strip_tags($value, $allowed);

        return preg_replace('/\s+/', ' ', trim($value)) ?: '';
    }
}
