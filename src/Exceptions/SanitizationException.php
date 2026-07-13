<?php

declare(strict_types=1);

namespace IvanBaric\Sanigen\Exceptions;

use RuntimeException;
use Throwable;

final class SanitizationException extends RuntimeException
{
    public function __construct(
        public readonly string $attribute,
        public readonly string $rule,
        ?Throwable $previous = null
    ) {
        parent::__construct(
            "Sanitization failed for attribute [{$attribute}] using rule [{$rule}].",
            0,
            $previous
        );
    }
}
