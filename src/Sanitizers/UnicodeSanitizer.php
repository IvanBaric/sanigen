<?php

declare(strict_types=1);

namespace IvanBaric\Sanigen\Sanitizers;

use InvalidArgumentException;
use IvanBaric\Sanigen\Sanitizers\Contracts\Sanitizer;
use Normalizer;

final class UnicodeSanitizer implements Sanitizer
{
    public function apply(string $value): string
    {
        $normalized = Normalizer::normalize($value, Normalizer::FORM_C);

        if ($normalized === false) {
            throw new InvalidArgumentException('Unicode NFC normalization failed for invalid UTF-8 input.');
        }

        return $normalized;
    }
}
