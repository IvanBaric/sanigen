<?php

declare(strict_types=1);

namespace IvanBaric\Sanigen\Support;

use InvalidArgumentException;

final class SanitizationRuleCompiler
{
    /**
     * @param  array<string, string>  $rules
     * @return array<string, list<CompiledSanitizationRule>>
     */
    public function compile(array $rules): array
    {
        $compiled = [];

        foreach ($rules as $path => $pipeline) {
            $rule = $this->compileRule($path, $pipeline);
            $compiled[$rule->root][] = $rule;
        }

        return $compiled;
    }

    private function compileRule(string $path, string $pipeline): CompiledSanitizationRule
    {
        $path = trim($path);

        if ($path === '') {
            throw new InvalidArgumentException('Sanitization path cannot be empty.');
        }

        $segments = explode('.', $path);

        foreach ($segments as $index => $segment) {
            if ($segment === '') {
                throw new InvalidArgumentException("Sanitization path [{$path}] contains an empty segment.");
            }

            if ($segment === '*') {
                if ($index === 0) {
                    throw new InvalidArgumentException("Sanitization path [{$path}] cannot use a wildcard as its root attribute.");
                }

                continue;
            }

            if (str_contains($segment, '*')) {
                throw new InvalidArgumentException("Sanitization path [{$path}] contains an invalid wildcard expression.");
            }

            if (preg_match('/\A[A-Za-z0-9_-]+\z/', $segment) !== 1) {
                throw new InvalidArgumentException("Sanitization path [{$path}] contains an invalid segment.");
            }
        }

        $pipeline = trim($pipeline);

        if (str_starts_with($pipeline, 'recursive:')) {
            $pipeline = trim(substr($pipeline, strlen('recursive:')));

            if ($pipeline === '') {
                throw new InvalidArgumentException("Attribute [{$segments[0]}] has an empty recursive sanitizer rule.");
            }
        }

        if ($pipeline === '' || array_filter(
            explode('|', $pipeline),
            static fn (string $rule): bool => trim($rule) !== '',
        ) === []) {
            throw new InvalidArgumentException("Sanitization path [{$path}] has an empty sanitizer pipeline.");
        }

        $root = array_shift($segments);

        return new CompiledSanitizationRule(
            path: $path,
            root: $root,
            segments: array_values($segments),
            pipeline: $pipeline,
        );
    }
}
