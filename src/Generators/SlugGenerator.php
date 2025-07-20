<?php

namespace IvanBaric\Sanigen\Generators;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use InvalidArgumentException;
use IvanBaric\Sanigen\Generators\Contracts\GeneratorContract;
use Ramsey\Uuid\Uuid;

/**
 * Generates a unique slug based on a source field.
 * 
 * This generator creates URL-friendly slugs from a specified source field
 * and ensures uniqueness by appending a configurable suffix if needed.
 */
class SlugGenerator implements GeneratorContract
{
    /**
     * The field to use as the source for the slug.
     */
    protected string $sourceField;

    /**
     * The type of suffix to use for ensuring uniqueness.
     * Options: 'increment', 'date', 'uuid', 'custom'
     */
    protected string $suffixType;

    /**
     * Format for date suffix (used when suffix_type is 'date').
     */
    protected string $dateFormat;

    /**
     * Create a new slug generator.
     *
     * @param string|null $param The parameter string from the generator key (format: "sourceField" or "sourceField,suffixType")
     * @param string|null $suffixType The type of suffix to use for ensuring uniqueness
     * @param string|null $dateFormat Format for date suffix (used when suffix_type is 'date')
     */
    public function __construct(
        ?string $param = null,
        ?string $suffixType = null,
        ?string $dateFormat = null
    ) {
        // Parse the parameter string
        if ($param) {
            $parts = explode(',', $param);
            $this->sourceField = $parts[0] ?? 'title';

            // If a suffix type is provided in the parameter, use it
            if (isset($parts[1])) {
                $suffixType = $parts[1];
            }
        } else {
            $this->sourceField = 'title';
        }

        $this->suffixType = $suffixType ?? config('sanigen.generator_settings.slugify.suffix_type', 'increment');
        $this->dateFormat = $dateFormat ?? config('sanigen.generator_settings.slugify.date_format', 'Y-m-d');
    }

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

        // Check if the slug already exists
        if ($model::query()->where($field, $slug)->exists()) {
            // Apply the appropriate suffix based on configuration
            $slug = $this->applySuffix($base, $field, $model);
        }

        return $slug;
    }

    /**
     * Apply a suffix to the base slug to ensure uniqueness.
     *
     * @param string $base The base slug
     * @param string $field The field name that will store the slug
     * @param object $model The model instance to generate the slug for
     * @return string The slug with suffix applied
     */
    protected function applySuffix(string $base, string $field, object $model): string
    {
        switch ($this->suffixType) {
            case 'date':
                return $this->applyDateSuffix($base, $field, $model);

            case 'uuid':
                return $this->applyUuidSuffix($base);

            case 'increment':
            default:
                return $this->applyIncrementSuffix($base, $field, $model);
        }
    }

    /**
     * Apply an incremental numeric suffix to ensure uniqueness.
     *
     * @param string $base The base slug
     * @param string $field The field name that will store the slug
     * @param object $model The model instance to generate the slug for
     * @return string The slug with incremental suffix
     */
    protected function applyIncrementSuffix(string $base, string $field, object $model): string
    {
        $i = 1;
        $slug = $base;

        while ($model::query()->where($field, $slug)->exists()) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }

    /**
     * Apply a date suffix to ensure uniqueness.
     *
     * @param string $base The base slug
     * @param string $field The field name that will store the slug
     * @param object $model The model instance to generate the slug for
     * @return string The slug with date suffix and incremental suffix if needed
     */
    protected function applyDateSuffix(string $base, string $field, object $model): string
    {
        $date = now()->format($this->dateFormat);
        $slug = "{$base}-{$date}";

        // Check if the slug with date suffix already exists
        if ($model::query()->where($field, $slug)->exists()) {
            // If it exists, add an incremental suffix
            $i = 1;
            $dateSlug = $slug;

            while ($model::query()->where($field, $dateSlug)->exists()) {
                $dateSlug = "{$slug}-{$i}";
                $i++;
            }

            return $dateSlug;
        }

        return $slug;
    }


    /**
     * Apply a UUID suffix to ensure uniqueness.
     *
     * @param string $base The base slug
     * @return string The slug with UUID suffix
     */
    protected function applyUuidSuffix(string $base): string
    {
        $uuid = Str::uuid()->toString();
        return "{$base}-{$uuid}";
    }
}
