<?php

namespace IvanBaric\Sanigen\Generators;

use Illuminate\Support\Carbon;
use IvanBaric\Sanigen\Generators\Contracts\GeneratorContract;

/**
 * Generates the current date and time.
 * 
 * This generator returns the current date and time as a Carbon instance
 * using Laravel's now() helper function.
 */
class NowGenerator implements GeneratorContract
{
    /**
     * Generate the current date and time.
     *
     * @param string $field The field name that will store the current timestamp
     * @param object $model The model instance to generate the value for
     * @return Carbon The current date and time as a Carbon instance
     */
    public function generate(string $field, object $model): Carbon
    {
        return now();
    }
}
