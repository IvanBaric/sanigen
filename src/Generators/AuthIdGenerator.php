<?php

namespace IvanBaric\Sanigen\Generators;

use RuntimeException;
use IvanBaric\Sanigen\Generators\Contracts\GeneratorContract;

/**
 * Generates a value from the currently authenticated user's ID.
 * 
 * This generator retrieves the ID of the authenticated user
 * and uses it as the generated value for a model field.
 */
class AuthIdGenerator implements GeneratorContract
{
    /**
     * Generate a value from the authenticated user's ID.
     *
     * @param string $field The field name that will store the user ID
     * @param object $model The model instance to generate the value for
     * @return int|null The ID of the authenticated user or null if no user is authenticated
     * @throws RuntimeException If strict mode is enabled and no user is authenticated
     */
    public function generate(string $field, object $model): ?int
    {
        $userId = auth()->id();
        
        // Optional: Uncomment to enable strict mode that requires authentication
        // if ($userId === null) {
        //     throw new RuntimeException("No authenticated user – cannot generate '{$field}' using auth ID.");
        // }
        
        return $userId;
    }
}