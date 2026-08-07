<?php

declare(strict_types=1);

namespace IvanBaric\Sanigen\Sanitizers;

use IvanBaric\Sanigen\Sanitizers\Contracts\Sanitizer;

final class DecimalSanitizer implements Sanitizer
{
    public function apply(string $value): string
    {
        $negative = preg_match('/-\s*[0-9]/u', $value) === 1;
        $clean = preg_replace('/[^0-9.,]+/u', '', $value) ?? '';

        if ($clean === '' || preg_match('/[0-9]/', $clean) !== 1) {
            return '';
        }

        $lastComma = strrpos($clean, ',');
        $lastDot = strrpos($clean, '.');

        if ($lastComma !== false && $lastDot !== false) {
            $decimalSeparator = $lastComma > $lastDot ? ',' : '.';
            $thousandsSeparator = $decimalSeparator === ',' ? '.' : ',';
            $clean = str_replace($thousandsSeparator, '', $clean);
            $clean = $this->normalizeSeparator($clean, $decimalSeparator);
        } elseif ($lastComma !== false) {
            $clean = $this->normalizeSeparator($clean, ',');
        } elseif ($lastDot !== false) {
            $clean = $this->normalizeSeparator($clean, '.');
        }

        return $negative ? '-'.$clean : $clean;
    }

    private function normalizeSeparator(string $value, string $separator): string
    {
        $parts = explode($separator, $value);

        if (count($parts) === 1) {
            return $value;
        }

        $decimal = array_pop($parts);
        $integer = implode('', $parts);

        return $decimal === '' ? $integer : $integer.'.'.$decimal;
    }
}
