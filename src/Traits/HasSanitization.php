<?php

namespace IvanBaric\Sanigen\Traits;

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

        if (is_array($value)) {
            $sanitizedArray = [];

            foreach ($value as $locale => $localeValue) {
                $sanitizedArray[$locale] = $this->sanitizeValue($key, $localeValue, $ruleSet);
            }

            return $sanitizedArray;
        }

        return $this->sanitizeValue($key, $value, $ruleSet);
    }

    protected function sanitizeValue(string $key, mixed $value, string $ruleSet): mixed
    {
        if (! is_scalar($value)) {
            return $value;
        }

        try {
            $stringValue = (string) $value;

            foreach (explode('|', $ruleSet) as $rule) {
                $rule = trim($rule);
                if ($rule === '') {
                    continue;
                }

                $sanitizer = SanitizerRegistry::resolve($rule);
                if ($sanitizer) {
                    $stringValue = $sanitizer->apply($stringValue);
                }
            }

            if ($stringValue === '' && $this->hasSanigenNumericCast($key)) {
                return null;
            }

            return $stringValue;
        } catch (\InvalidArgumentException $e) {
            throw $e;
        } catch (Throwable $e) {
            if (function_exists('logger')) {
                logger()->error("Sanitization failed for attribute {$key}: ".$e->getMessage());
            }

            return $value;
        }
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
