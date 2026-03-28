<?php

namespace IvanBaric\Sanigen\Sanitizers;

use IvanBaric\Sanigen\Sanitizers\Contracts\Sanitizer;

final class PhoneCleanSanitizer implements Sanitizer
{
    public function apply(string $value): string
    {
        $clean = preg_replace('/[^\d+]/', '', trim($value)) ?: '';

        if (substr_count($clean, '+') > 1) {
            $clean = '+'.preg_replace('/\D+/', '', $clean);
        }

        return $clean;
    }
}
