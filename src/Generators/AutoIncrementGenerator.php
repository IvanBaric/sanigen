<?php

namespace IvanBaric\Sanigen\Generators;

use IvanBaric\Sanigen\Generators\Contracts\GeneratorContract;

/**
 * Generates auto-incrementing values for model attributes.
 * 
 * This generator finds the maximum current value for a field across all
 * model instances and increments it by 1, starting from 1 if no records exist.
 */
class AutoIncrementGenerator implements GeneratorContract
{
    /**
     * Generate the next auto-incrementing value for the specified field.
     *
     * @param string $field The field name that will store the auto-incrementing value
     * @param object $model The model instance to generate the value for
     * @return int The next available integer value (current max + 1)
     */
    public function generate(string $field, object $model): int
    {
        $max = $model::query()->max($field);

        return is_numeric($max) ? $max + 1 : 1;
    }
}
