<?php

namespace IvanBaric\Sanigen\Sanitizers;

use IvanBaric\Sanigen\Sanitizers\Contracts\Sanitizer;

final class StripEmojiSanitizer implements Sanitizer
{
    public function apply(string $value): string
    {
        return preg_replace(
            '/[\x{1F600}-\x{1F64F}]|'.
            '[\x{1F300}-\x{1F5FF}]|'.
            '[\x{1F680}-\x{1F6FF}]|'.
            '[\x{2600}-\x{26FF}]|'.
            '[\x{2700}-\x{27BF}]|'.
            '[\x{1F900}-\x{1F9FF}]|'.
            '[\x{1FA70}-\x{1FAFF}]|'.
            '[\x{1F1E6}-\x{1F1FF}]'.
            '/u',
            '',
            $value
        ) ?? $value;
    }
}
