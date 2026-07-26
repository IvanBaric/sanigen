<?php

namespace IvanBaric\Sanigen\Traits;

use InvalidArgumentException;
use IvanBaric\Sanigen\Exceptions\SanitizationException;
use IvanBaric\Sanigen\Registries\SanitizerRegistry;
use IvanBaric\Sanigen\Resolvers\ModelRuleResolver;
use Throwable;

trait HasSanitization
{
    protected array $sanigenNumericCastTypes = [
        'int',
        'integer',
        'real',
        'float',
        'double',
        'decimal',
    ];

    /**
     * @return array<string, string>
     */
    protected function getSanigenSanitizeRules(): array
    {
        return ModelRuleResolver::sanitizeRules($this);
    }

    protected function sanitizeAttribute(string $key, mixed $value): mixed
    {
        if (config('sanigen.enabled', true) === false || $value === null) {
            return $value;
        }

        $rules = $this->getSanigenSanitizeRules();
        $ruleSet = $rules[$key] ?? null;

        if (! is_string($ruleSet) || $ruleSet === '') {
            return $value;
        }

        $items = 0;

        return $this->sanitizeStructuredValue($key, $value, $ruleSet, 0, $items);
    }

    protected function sanitizeStructuredValue(
        string $key,
        mixed $value,
        string $ruleSet,
        int $depth = 0,
        int &$items = 0,
    ): mixed {
        if ($value === null) {
            return null;
        }

        $maxDepth = max(1, (int) config('sanigen.max_nested_depth', 8));

        if ($depth > $maxDepth) {
            throw new InvalidArgumentException("Attribute [{$key}] exceeds the {$maxDepth} level nesting limit.");
        }

        if (is_array($value)) {
            $sanitizedArray = [];
            $maxItems = max(1, (int) config('sanigen.max_nested_items', 500));

            foreach ($value as $nestedKey => $nestedValue) {
                $items++;

                if ($items > $maxItems) {
                    throw new InvalidArgumentException("Attribute [{$key}] exceeds the {$maxItems} item limit.");
                }

                $sanitizedArray[$nestedKey] = $this->sanitizeStructuredValue(
                    $key,
                    $nestedValue,
                    $ruleSet,
                    $depth + 1,
                    $items,
                );
            }

            return $sanitizedArray;
        }

        return $this->sanitizeValue($key, $value, $ruleSet);
    }

    protected function sanitizeValue(string $key, mixed $value, string $ruleSet): mixed
    {
        if (! is_scalar($value)) {
            throw new InvalidArgumentException("Attribute [{$key}] contains a non-scalar value that cannot be sanitized.");
        }

        $stringValue = (string) $value;
        $maxLength = max(1, (int) config('sanigen.max_scalar_input_length', 65535));

        if (strlen($stringValue) > $maxLength) {
            throw new InvalidArgumentException("Attribute [{$key}] exceeds the {$maxLength} byte input limit.");
        }

        foreach (explode('|', $ruleSet) as $rule) {
            $rule = trim($rule);
            if ($rule === '') {
                continue;
            }

            try {
                $sanitizer = SanitizerRegistry::resolve($rule);
                if ($sanitizer) {
                    $stringValue = $sanitizer->apply($stringValue);
                }
            } catch (InvalidArgumentException $e) {
                throw $e;
            } catch (Throwable $e) {
                return $this->handleSanitizationFailure($key, $rule, $value, $e);
            }
        }

        if ($stringValue === '' && $this->hasSanigenNumericCast($key)) {
            return null;
        }

        return $stringValue;
    }

    protected function handleSanitizationFailure(string $key, string $rule, mixed $originalValue, Throwable $e): mixed
    {
        report($e);
        $mode = config('sanigen.failure_mode', 'throw');

        if ($mode === 'throw') {
            throw new SanitizationException($key, $rule, $e);
        }

        if ($mode === 'null') {
            return null;
        }

        if ($mode === 'original') {
            return $originalValue;
        }

        throw new InvalidArgumentException("Invalid Sanigen failure mode [{$mode}]. Supported modes are: throw, null, original.");
    }

    public function sanitizeAttributes(): bool
    {
        if (config('sanigen.enabled', true) === false) {
            return false;
        }

        $rules = $this->getSanigenSanitizeRules();

        if ($rules === []) {
            return false;
        }

        $updated = false;

        foreach ($rules as $attribute => $_ruleSet) {
            $usedRawFallback = false;

            try {
                $originalValue = $this->{$attribute};
            } catch (Throwable $e) {
                report($e);
                $originalValue = $this->getRawOriginal($attribute);
                $usedRawFallback = true;
            }

            if ($originalValue === null) {
                continue;
            }

            $sanitizedValue = $this->sanitizeAttribute($attribute, $originalValue);

            if ($sanitizedValue !== $originalValue) {
                $this->{$attribute} = $sanitizedValue;

                if ($usedRawFallback) {
                    $this->original[$attribute] = null;
                }

                $updated = true;
            }
        }

        return $updated;
    }

    protected function hasSanigenNumericCast(string $key): bool
    {
        $cast = $this->getCasts()[$key] ?? null;
        if (! is_string($cast) || $cast === '') {
            return false;
        }

        $castType = strtolower(strtok($cast, ':'));

        return in_array($castType, $this->sanigenNumericCastTypes, true);
    }

    public function setAttribute($key, $value)
    {
        if (is_string($key)) {
            $value = $this->sanitizeAttribute($key, $value);
        }

        return parent::setAttribute($key, $value);
    }
}
