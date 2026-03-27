<?php

namespace IvanBaric\Sanigen\Traits;

use IvanBaric\Sanigen\Registries\GeneratorRegistry;

/**
 * Provides automatic value generation for model attributes.
 * 
 * This trait allows models to define a $generate property that maps attributes
 * to generator keys. When a model is being created, the specified generators
 * will be used to populate empty attributes.
 */
trait HasGenerators
{
    /**
     * Boot the trait by registering a creating event listener.
     * 
     * This method is automatically called by Laravel when the model is booted.
     */
    public static function bootHasGenerators(): void
    {
        // Only register the event listener if the package is enabled
        if (config('sanigen.enabled', true) === false) {
            return;
        }
        
        static::creating(function ($model) {
            // Process each attribute that needs generation
            foreach ($model->generate ?? [] as $attribute => $generatorKey) {
                // Skip attributes that already have a value
                if (!is_null($model->{$attribute})) {
                    continue;
                }

                // Resolve and apply the generator
                $generator = GeneratorRegistry::resolve($generatorKey);
                if ($generator) {
                    $model->{$attribute} = $generator->generate($attribute, $model);
                }
            }
        });

        static::updating(function ($model) {
            if (!static::shouldUpdateSlugsOnSave($model)) {
                return;
            }

            foreach ($model->generate ?? [] as $attribute => $generatorKey) {
                if (!static::isSlugGenerator($generatorKey)) {
                    continue;
                }

                $sourceField = static::extractSlugSourceField($generatorKey);
                if (!$model->isDirty($sourceField)) {
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
