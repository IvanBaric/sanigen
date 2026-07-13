<?php

namespace IvanBaric\Sanigen\Sanitizers;

use InvalidArgumentException;
use IvanBaric\Sanigen\Sanitizers\Contracts\Sanitizer;

/**
 * Sanitizes a string to a valid URL with an allowed scheme.
 */
final class UrlSanitizer implements Sanitizer
{
    public function apply(string $value): string
    {
        $value = trim($value);

        if ($value === '') {
            return '';
        }

        if (str_starts_with($value, '//')) {
            $value = $this->defaultScheme().':'.$value;
        } elseif (! preg_match('/^[a-z][a-z0-9+\-.]*:/i', $value)) {
            $value = $this->defaultScheme().'://'.ltrim($value, '/');
        }

        if (filter_var($value, FILTER_VALIDATE_URL) === false) {
            return '';
        }

        $scheme = parse_url($value, PHP_URL_SCHEME);

        if (! is_string($scheme) || ! in_array(strtolower($scheme), $this->allowedSchemes(), true)) {
            return '';
        }

        return preg_replace('/^[a-z][a-z0-9+\-.]*:/i', strtolower($scheme).':', $value, 1) ?? '';
    }

    /**
     * @return list<string>
     */
    private function allowedSchemes(): array
    {
        $schemes = config('sanigen.allowed_url_schemes', ['http', 'https']);

        if (! is_array($schemes)) {
            throw new InvalidArgumentException('Sanigen allowed_url_schemes must be an array.');
        }

        $schemes = array_values(array_unique(array_filter(
            array_map(static fn (mixed $scheme): string => strtolower(trim((string) $scheme)), $schemes),
            static fn (string $scheme): bool => $scheme !== ''
        )));

        if ($schemes === []) {
            throw new InvalidArgumentException('Sanigen allowed_url_schemes cannot be empty.');
        }

        return $schemes;
    }

    private function defaultScheme(): string
    {
        $scheme = strtolower(trim((string) config('sanigen.default_url_scheme', 'https')));

        if ($scheme === '' || ! in_array($scheme, $this->allowedSchemes(), true)) {
            throw new InvalidArgumentException('Sanigen default_url_scheme must be included in allowed_url_schemes.');
        }

        return $scheme;
    }
}
