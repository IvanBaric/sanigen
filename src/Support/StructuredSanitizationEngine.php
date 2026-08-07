<?php

declare(strict_types=1);

namespace IvanBaric\Sanigen\Support;

use Closure;
use InvalidArgumentException;

final class StructuredSanitizationEngine
{
    /**
     * @param  list<CompiledSanitizationRule>  $rules
     * @param  Closure(string, string, string): mixed  $sanitizeString
     */
    public function sanitize(string $root, mixed $value, array $rules, Closure $sanitizeString): mixed
    {
        $items = 0;

        return $this->walk(
            root: $root,
            value: $value,
            path: [],
            rules: $rules,
            sanitizeString: $sanitizeString,
            depth: 0,
            items: $items,
        );
    }

    /**
     * @param  list<string>  $path
     * @param  list<CompiledSanitizationRule>  $rules
     * @param  Closure(string, string, string): mixed  $sanitizeString
     */
    private function walk(
        string $root,
        mixed $value,
        array $path,
        array $rules,
        Closure $sanitizeString,
        int $depth,
        int &$items,
    ): mixed {
        $fullPath = $this->displayPath($root, $path);
        $maxDepth = max(1, (int) config('sanigen.max_nested_depth', 8));

        if ($depth > $maxDepth) {
            throw new InvalidArgumentException("Attribute [{$root}] exceeds the {$maxDepth} level nesting limit at [{$fullPath}].");
        }

        if (is_array($value)) {
            $sanitized = [];
            $maxItems = max(1, (int) config('sanigen.max_nested_items', 500));

            foreach ($value as $key => $nestedValue) {
                $items++;

                if ($items > $maxItems) {
                    throw new InvalidArgumentException("Attribute [{$root}] exceeds the {$maxItems} item limit at [{$fullPath}].");
                }

                $sanitized[$key] = $this->walk(
                    root: $root,
                    value: $nestedValue,
                    path: [...$path, (string) $key],
                    rules: $rules,
                    sanitizeString: $sanitizeString,
                    depth: $depth + 1,
                    items: $items,
                );
            }

            return $sanitized;
        }

        if ($value === null || is_int($value) || is_float($value) || is_bool($value)) {
            return $value;
        }

        if (! is_string($value)) {
            throw new InvalidArgumentException("Attribute [{$root}] contains an unsupported value at [{$fullPath}].");
        }

        $maxLength = max(1, (int) config('sanigen.max_scalar_input_length', 65535));

        if (strlen($value) > $maxLength) {
            throw new InvalidArgumentException("Attribute [{$root}] exceeds the {$maxLength} byte input limit at [{$fullPath}].");
        }

        $pipeline = $this->pipelineFor($root, $path, $rules);

        return $pipeline === null ? $value : $sanitizeString($value, $pipeline, $fullPath);
    }

    /**
     * @param  list<string>  $path
     * @param  list<CompiledSanitizationRule>  $rules
     */
    private function pipelineFor(string $root, array $path, array $rules): ?string
    {
        $matches = array_values(array_filter(
            $rules,
            static fn (CompiledSanitizationRule $rule): bool => $rule->matches($path),
        ));

        if ($matches === []) {
            return null;
        }

        $maximumLength = max(array_map(
            static fn (CompiledSanitizationRule $rule): int => $rule->length(),
            $matches,
        ));
        $matches = array_values(array_filter(
            $matches,
            static fn (CompiledSanitizationRule $rule): bool => $rule->length() === $maximumLength,
        ));

        $maximumLiterals = max(array_map(
            static fn (CompiledSanitizationRule $rule): int => $rule->literalCount(),
            $matches,
        ));
        $matches = array_values(array_filter(
            $matches,
            static fn (CompiledSanitizationRule $rule): bool => $rule->literalCount() === $maximumLiterals,
        ));

        $pipelines = array_values(array_unique(array_map(
            static fn (CompiledSanitizationRule $rule): string => $rule->pipeline,
            $matches,
        )));

        if (count($pipelines) > 1) {
            $paths = implode(', ', array_map(
                static fn (CompiledSanitizationRule $rule): string => $rule->path,
                $matches,
            ));
            $fullPath = $this->displayPath($root, $path);

            throw new InvalidArgumentException("Conflicting sanitizer pipelines match [{$fullPath}] with equal specificity: {$paths}.");
        }

        return $pipelines[0];
    }

    /** @param list<string> $path */
    private function displayPath(string $root, array $path): string
    {
        return $path === [] ? $root : $root.'.'.implode('.', $path);
    }
}
