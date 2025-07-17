<?php

namespace IvanBaric\Sanigen\Generators;

use Illuminate\Support\Facades\Auth;
use RuntimeException;
use IvanBaric\Sanigen\Generators\Contracts\GeneratorContract;

/**
 * Generates a value from the currently authenticated user's property.
 * 
 * This generator retrieves a specified property from the authenticated user
 * and uses it as the generated value for a model field.
 */
class UserPropertyGenerator implements GeneratorContract
{
    /**
     * Create a new user property generator.
     *
     * @param string $property The user property to retrieve (defaults to 'id')
     */
    public function __construct(protected string $property = 'id') {}

    /**
     * Generate a value from the authenticated user's property.
     *
     * @param string $field The field name that will store the user property
     * @param object $model The model instance to generate the value for
     * @return mixed The value of the specified user property
     * @throws RuntimeException If no user is authenticated or the property doesn't exist
     */
    public function generate(string $field, object $model): mixed
    {
        // Get the currently authenticated user
        $user = Auth::user();

        // Ensure a user is authenticated
        if (! $user) {
            throw new RuntimeException("No authenticated user – cannot generate '{$field}' from user:{$this->property}.");
        }

        // Ensure the requested property exists on the user
        if (! isset($user->{$this->property})) {
            throw new RuntimeException("Property '{$this->property}' does not exist on the user – cannot generate '{$field}'.");
        }

        return $user->{$this->property};
    }
}