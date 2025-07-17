<?php

namespace IvanBaric\Sanigen\Generators;

use Illuminate\Support\Str;
use RuntimeException;
use IvanBaric\Sanigen\Generators\Contracts\GeneratorContract;

/**
 * Generates unique codes for model attributes.
 * 
 * This generator creates random strings of a specified length and ensures
 * they are unique within the model's table by checking the database.
 * It will make multiple attempts to find a unique code before failing.
 */
class UniqueCodeGenerator implements GeneratorContract
{
    /**
     * Create a new unique code generator.
     *
     * @param int $length The length of the unique code to generate
     * @param int $maxAttempts The maximum number of attempts to generate a unique code
     */
    public function __construct(protected int $length = 6, protected int $maxAttempts = 10) {}

    /**
     * Generate a unique code for the specified field.
     *
     * @param string $field The field name that will store the unique code
     * @param object $model The model instance to generate the value for
     * @return string The generated unique code
     * @throws RuntimeException If unable to generate a unique code after maximum attempts
     */
    public function generate(string $field, object $model): string
    {
        $attempts = 0;

        do {
            $code = Str::random($this->length);
            $exists = $model::query()->where($field, $code)->exists();
            $attempts++;
        } while ($exists && $attempts < $this->maxAttempts);

        if ($exists) {
            throw new RuntimeException("Unable to generate unique code for [$field] after {$this->maxAttempts} attempts.");
        }

        return $code;
    }
}
