<?php

namespace IvanBaric\Sanigen\Generators;

use Illuminate\Support\Str;
use IvanBaric\Sanigen\Generators\Contracts\GeneratorContract;

/**
 * Generates ULID (Universally Unique Lexicographically Sortable Identifier) values.
 * 
 * ULIDs are 26-character identifiers that combine a timestamp with random data,
 * making them both unique and sortable by creation time. They are case-insensitive
 * and use Crockford's base32 encoding for better readability and error resistance.
 * 
 * Advantages over UUIDs:
 * - Lexicographically sortable (newer values sort after older values)
 * - More compact representation (26 characters vs 36 for UUID)
 * - No special characters (only alphanumeric)
 * - Case insensitive
 */
class UlidGenerator implements GeneratorContract
{
    /**
     * Generate a new ULID value.
     *
     * @param string $field The field name that will store the ULID
     * @param object $model The model instance to generate the value for
     * @return string The generated ULID as a string
     */
    public function generate(string $field, object $model): string
    {
        return (string) Str::ulid();
    }
}
