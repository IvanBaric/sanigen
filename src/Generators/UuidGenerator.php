<?php

namespace IvanBaric\Sanigen\Generators;

use Illuminate\Support\Str;
use IvanBaric\Sanigen\Generators\Contracts\GeneratorContract;

/**
 * Generates UUID (Universally Unique Identifier) values.
 * 
 * UUIDs are 36-character identifiers (including hyphens) that are designed to be
 * globally unique without requiring a central registration authority. Laravel
 * generates version 4 UUIDs, which are based on random numbers.
 * 
 * UUIDs are ideal for:
 * - Primary keys that need to be generated before database insertion
 * - Distributed systems where uniqueness across multiple systems is required
 * - Public-facing IDs where sequential numbers would expose sensitive information
 */
class UuidGenerator implements GeneratorContract
{
    /**
     * Generate a new UUID value.
     *
     * @param string $field The field name that will store the UUID
     * @param object $model The model instance to generate the value for
     * @return string The generated UUID as a string
     */
    public function generate(string $field, object $model): string
    {
        return (string) Str::uuid();
    }
}