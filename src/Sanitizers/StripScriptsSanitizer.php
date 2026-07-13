<?php

namespace IvanBaric\Sanigen\Sanitizers;

use IvanBaric\Sanigen\Sanitizers\Contracts\Sanitizer;

final class StripScriptsSanitizer implements Sanitizer
{
    public function apply(string $value): string
    {
        if ($value === '') {
            return '';
        }

        $value = (new SafeHtmlSanitizer)->apply($value);
        $value = strip_tags($value, config('sanigen.allowed_html_tags', ''));
        $value = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, config('sanigen.encoding', 'UTF-8'));
        $value = $this->removeLegacyScriptTextPatterns($value);

        return preg_replace('/\s+/', ' ', trim($value)) ?: '';
    }

    private function removeLegacyScriptTextPatterns(string $value): string
    {
        $value = preg_replace('/alert\s*\([^)]*\)/is', '', $value) ?? $value;
        $value = preg_replace('/eval\s*\([^)]*\)/is', '', $value) ?? $value;
        $value = preg_replace('/atob\s*\([^)]*\)/is', '', $value) ?? $value;
        $value = preg_replace('/\b(?:fetch|setTimeout|setInterval|XMLHttpRequest|importScripts)\s*\([^)]*\)/is', '', $value) ?? $value;
        $value = preg_replace('/\bnew\s+Function\s*\([^)]*\)/is', '', $value) ?? $value;
        $value = preg_replace('/\bFunction\s*\([^)]*\)/is', '', $value) ?? $value;
        $value = preg_replace('/\b(?:document\.cookie|window\.location|location\.href)\s*=\s*[^;]+;?/is', '', $value) ?? $value;

        return preg_replace('/\bdocument\.cookie\b/is', '', $value) ?? $value;
    }
}
