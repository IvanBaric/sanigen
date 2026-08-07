<?php

declare(strict_types=1);

namespace IvanBaric\Sanigen\Traits;

use InvalidArgumentException;
use IvanBaric\Sanigen\Exceptions\SanitizationException;
use IvanBaric\Sanigen\Registries\SanitizerRegistry;
use IvanBaric\Sanigen\Resolvers\ModelRuleResolver;
use IvanBaric\Sanigen\Support\CompiledSanitizationRule;
use IvanBaric\Sanigen\Support\SanitizationRuleCompiler;
use IvanBaric\Sanigen\Support\StructuredSanitizationEngine;
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

        $rulesByRoot = app(SanitizationRuleCompiler::class)->compile($this->getSanigenSanitizeRules());
        $rules = $rulesByRoot[$key] ?? [];

        return $rules === [] ? $value : $this->sanitizeRootValue($key, $value, $rules);
    }

    /**
     * @param  list<CompiledSanitizationRule>  $rules
     */
    private function sanitizeRootValue(string $root, mixed $value, array $rules): mixed
    {
        return app(StructuredSanitizationEngine::class)->sanitize(
            root: $root,
            value: $value,
            rules: $rules,
            sanitizeString: fn (string $string, string $pipeline, string $path): mixed => $this->sanitizeValue(
                $path,
                $string,
                $pipeline,
            ),
        );
    }

    protected function sanitizeValue(string $key, mixed $value, string $ruleSet): mixed
    {
        if (! is_string($value)) {
            throw new InvalidArgumentException("Attribute [{$key}] contains a value that cannot be sanitized as a string.");
        }

        $stringValue = $value;

        foreach (explode('|', $ruleSet) as $rule) {
            $rule = trim($rule);

            if ($rule === '') {
                continue;
            }

            try {
                $sanitizer = SanitizerRegistry::resolve($rule);

                if ($sanitizer !== null) {
                    $stringValue = $sanitizer->apply($stringValue);
                }
            } catch (InvalidArgumentException $exception) {
                throw $exception;
            } catch (Throwable $exception) {
                return $this->handleSanitizationFailure($key, $rule, $value, $exception);
            }
        }

        $root = strtok($key, '.');

        if ($stringValue === '' && is_string($root) && $this->hasSanigenNumericCast($root)) {
            return null;
        }

        return $stringValue;
    }

    protected function handleSanitizationFailure(string $key, string $rule, mixed $originalValue, Throwable $exception): mixed
    {
        report($exception);
        $mode = config('sanigen.failure_mode', 'throw');

        if ($mode === 'throw') {
            throw new SanitizationException($key, $rule, $exception);
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

        $rulesByRoot = app(SanitizationRuleCompiler::class)->compile($this->getSanigenSanitizeRules());

        if ($rulesByRoot === []) {
            return false;
        }

        $updated = false;

        foreach ($rulesByRoot as $root => $rules) {
            $usedRawFallback = false;

            try {
                $originalValue = $this->isSanigenTranslatableAttribute($root)
                    && method_exists($this, 'getTranslations')
                        ? $this->getTranslations($root)
                        : $this->{$root};
            } catch (Throwable $exception) {
                report($exception);
                $originalValue = $this->getRawOriginal($root);
                $usedRawFallback = true;
            }

            if ($originalValue === null) {
                continue;
            }

            $sanitizedValue = $this->sanitizeRootValue($root, $originalValue, $rules);

            if ($sanitizedValue === $originalValue) {
                continue;
            }

            parent::setAttribute($root, $sanitizedValue);

            if ($usedRawFallback) {
                $this->original[$root] = null;
            }

            $updated = true;
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
            if ($this->isSanigenTranslatableAttribute($key)
                && method_exists($this, 'setSanigenTranslatableAttribute')) {
                return $this->setSanigenTranslatableAttribute($key, $value);
            }

            $value = $this->sanitizeAttribute($key, $value);

            if ($this->isSanigenTranslatableAttribute($key)) {
                if (is_array($value) && (! array_is_list($value) || $value === [])) {
                    return $this->setTranslations($key, $value);
                }

                return $this->setTranslation($key, $this->getLocale(), $value);
            }
        }

        return parent::setAttribute($key, $value);
    }

    private function isSanigenTranslatableAttribute(string $key): bool
    {
        return method_exists($this, 'isTranslatableAttribute')
            && method_exists($this, 'setTranslations')
            && method_exists($this, 'setTranslation')
            && method_exists($this, 'getLocale')
            && $this->isTranslatableAttribute($key);
    }
}
