<?php

namespace IvanBaric\Sanigen\Resolvers;

use IvanBaric\Sanigen\Attributes\Generate;
use IvanBaric\Sanigen\Attributes\Sanitize;
use ReflectionClass;
use Throwable;

final class ModelRuleResolver
{
    /**
     * Resolve sanitization rules with this priority:
     * 1) model property $sanitize
     * 2) class-level #[Sanitize([...])]
     * 3) config defaults (sanigen.sanitize_defaults)
     *
     * @return array<string, string>
     */
    public static function sanitizeRules(object $model): array
    {
        return array_replace(
            self::configRules('sanitize_defaults'),
            self::attributeRules($model, Sanitize::class, 'rules'),
            self::propertyRules($model, 'sanitize')
        );
    }

    /**
     * Resolve generator rules with this priority:
     * 1) model property $generate
     * 2) class-level #[Generate([...])]
     * 3) config defaults (sanigen.generate_defaults)
     *
     * @return array<string, string>
     */
    public static function generateRules(object $model): array
    {
        return array_replace(
            self::configRules('generate_defaults'),
            self::attributeRules($model, Generate::class, 'rules'),
            self::propertyRules($model, 'generate')
        );
    }

    /**
     * @return array<string, string>
     */
    private static function configRules(string $key): array
    {
        return self::normalizeRules(config("sanigen.{$key}", []));
    }

    /**
     * @return array<string, string>
     */
    private static function propertyRules(object $model, string $property): array
    {
        try {
            $reflection = new ReflectionClass($model);

            while ($reflection) {
                if ($reflection->hasProperty($property)) {
                    $ruleProperty = $reflection->getProperty($property);
                    $ruleProperty->setAccessible(true);

                    return self::normalizeRules($ruleProperty->getValue($model));
                }

                $reflection = $reflection->getParentClass();
            }
        } catch (Throwable) {
            return [];
        }

        return [];
    }

    /**
     * @return array<string, string>
     */
    private static function attributeRules(object $model, string $attributeClass, string $attributeProperty): array
    {
        $reflection = new ReflectionClass($model);
        $attributes = $reflection->getAttributes($attributeClass);

        if ($attributes === []) {
            return [];
        }

        $rules = [];

        foreach ($attributes as $attribute) {
            $instance = $attribute->newInstance();
            $value = $instance->{$attributeProperty} ?? [];
            $rules = array_replace($rules, self::normalizeRules($value));
        }

        return $rules;
    }

    /**
     * @return array<string, string>
     */
    private static function normalizeRules(mixed $rules): array
    {
        if (! is_array($rules)) {
            return [];
        }

        $normalized = [];

        foreach ($rules as $attribute => $ruleSet) {
            if (! is_string($attribute) || ! is_string($ruleSet)) {
                continue;
            }

            $attribute = trim($attribute);
            $ruleSet = trim($ruleSet);

            if ($attribute === '' || $ruleSet === '') {
                continue;
            }

            $normalized[$attribute] = $ruleSet;
        }

        return $normalized;
    }
}
