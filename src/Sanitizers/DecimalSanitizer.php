<?php

namespace IvanBaric\Sanigen\Sanitizers;

use IvanBaric\Sanigen\Sanitizers\Contracts\Sanitizer;

final class DecimalSanitizer implements Sanitizer
{
    public function apply(string $value): string
    {
        $normalized = str_replace(',', '.', $value);
        $clean = preg_replace('/[^0-9\.]+/', '', $normalized) ?? '';

        $parts = explode('.', $clean);
        if (count($parts) <= 2) {
            return $clean;
        }

        $decimal = array_pop($parts);
        $integer = implode('', $parts);

        if ($decimal === '') {
            return $integer;
        }

        return $integer.'.'.$decimal;
    }
}
