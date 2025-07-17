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

    }
}
