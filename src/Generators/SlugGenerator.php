<?php

namespace IvanBaric\Sanigen\Generators;

use Illuminate\Support\Str;
use InvalidArgumentException;
use IvanBaric\Sanigen\Generators\Contracts\GeneratorContract;

/**
 * Generates a unique slug based on a source field.
 * 
 * This generator creates URL-friendly slugs from a specified source field
 * and ensures uniqueness by appending a numeric suffix if needed.
 */
class SlugGenerator implements GeneratorContract
{
    /**
     * Create a new slug generator.
     *
     * @param string $sourceField The model field to use as the source for the slug
     */
    public function __construct(protected string $sourceField = 'title') {}

    /**
     * Generate a unique slug for the specified field.
     *
     * @param string $field The field name that will store the slug
     * @param object $model The model instance to generate the slug for
     * @return string The generated unique slug
     * @throws InvalidArgumentException If the source field is empty or not set
     */
    public function generate(string $field, object $model): string
    {
        // Create base slug from the source field
        $base = Str::slug($model->{$this->sourceField} ?? '');

        // Validate that we have a non-empty source value
        if ($base === '') {
            throw new InvalidArgumentException("Source field [{$this->sourceField}] is empty or not set.");
        }

        // Start with the base slug
        $slug = $base;
        $i = 1;

        // Ensure uniqueness by checking the database and appending a numeric suffix if needed
        while ($model::query()->where($field, $slug)->exists()) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }
}
