<?php

namespace IvanBaric\Sanigen\Generators;

use Illuminate\Support\Str;
use Ramsey\Uuid\Uuid;
use IvanBaric\Sanigen\Generators\Contracts\GeneratorContract;

/**
 * Generates UUID (Universally Unique Identifier) values.
 * 
 * UUIDs are 36-character identifiers (including hyphens) that are designed to be
 * globally unique without requiring a central registration authority.
 * 
 * Supported UUID versions:
 * - v4: Random-based UUID (default)
 * - v7: Time-ordered UUID with Unix timestamp and random data
 * - v8: Custom UUID format with vendor-specific data
 * 
 * UUIDs are ideal for:
 * - Primary keys that need to be generated before database insertion
 * - Distributed systems where uniqueness across multiple systems is required
 * - Public-facing IDs where sequential numbers would expose sensitive information
 */
class UuidGenerator implements GeneratorContract
{
    /**
     * Create a new UUID generator.
     *
     * @param string|null $version The UUID version to generate (v4, v7, v8). Defaults to v4.
     */
    public function __construct(protected ?string $version = 'v4') {}

    /**
     * Generate a new UUID value.
     *
     * @param string $field The field name that will store the UUID
     * @param object $model The model instance to generate the value for
     * @return string The generated UUID as a string
     */
    public function generate(string $field, object $model): string
    {
        // If version is null, use the default v4
        $version = $this->version ?? 'v4';

        return match (strtolower($version)) {
            'v7' => (string) Uuid::uuid7(),
            'v8' => (string) Uuid::uuid8(Uuid::NAMESPACE_DNS, gethostname()),
            default => (string) Str::uuid(), // v4 is the default
        };
    }
}
