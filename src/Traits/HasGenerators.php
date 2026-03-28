<?php

namespace IvanBaric\Sanigen\Traits;

use IvanBaric\Sanigen\Registries\GeneratorRegistry;
use IvanBaric\Sanigen\Resolvers\ModelRuleResolver;

trait HasGenerators
{
    public static function bootHasGenerators(): void
    {
        if (config('sanigen.enabled', true) === false) {
            return;
        }

        static::creating(function ($model) {
            foreach (ModelRuleResolver::generateRules($model) as $attribute => $generatorKey) {
                if (! is_null($model->{$attribute})) {
                    continue;
                }

                $generator = GeneratorRegistry::resolve($generatorKey);
                if ($generator) {
                    $model->{$attribute} = $generator->generate($attribute, $model);
                }
            }
        });

        static::updating(function ($model) {
            if (! static::shouldUpdateSlugsOnSave($model)) {
                return;
            }

            foreach (ModelRuleResolver::generateRules($model) as $attribute => $generatorKey) {
                if (! static::isSlugGenerator($generatorKey)) {
                    continue;
                }

                $sourceField = static::extractSlugSourceField($generatorKey);
                if (! $model->isDirty($sourceField)) {
                    continue;
                }

                $generator = GeneratorRegistry::resolve($generatorKey);
                if ($generator) {
                    $model->{$attribute} = $generator->generate($attribute, $model);
                }
            }
        });
    }

    protected static function isSlugGenerator(string $generatorKey): bool
    {
        return str_starts_with($generatorKey, 'slugify');
    }

    protected static function extractSlugSourceField(string $generatorKey): string
    {
        [, $param] = array_pad(explode(':', $generatorKey, 2), 2, null);
        if ($param === null || $param === '') {
            return 'title';
        }

        $parts = explode(',', $param);

        return $parts[0] !== '' ? $parts[0] : 'title';
    }

    protected static function shouldUpdateSlugsOnSave(object $model): bool
    {
        if (property_exists($model, 'slugUpdatesOnSave')) {
            return (bool) $model->slugUpdatesOnSave;
        }

        return (bool) config('sanigen.generator_settings.slugify.slug_updates_on_save', false);
    }
}
