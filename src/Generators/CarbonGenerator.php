<?php

namespace IvanBaric\Sanigen\Generators;

use Carbon\Carbon;
use RuntimeException;
use IvanBaric\Sanigen\Generators\Contracts\GeneratorContract;

/**
 * Generates date/time values with a specified offset from the current time.
 * 
 * This generator creates Carbon date objects by applying a date/time modifier
 * to the current time, such as '+7 days', '-2 weeks', etc.
 */
class CarbonGenerator implements GeneratorContract
{
    /**
     * Create a new Carbon date generator.
     *
     * @param string $modifier The date/time modifier string (e.g., '+7 days', '-2 weeks')
     */
    public function __construct(protected string $modifier = '+7 days') {}

    /**
     * Generate a date/time value with the specified offset from now.
     *
     * @param string $field The field name that will store the date value
     * @param object $model The model instance to generate the value for
     * @return Carbon The generated Carbon date instance
     * @throws RuntimeException If the date modifier is invalid
     */
    public function generate(string $field, object $model): Carbon
    {
        $date = Carbon::now()->modify($this->modifier);

        if (!$date) {
            throw new RuntimeException("CarbonGenerator: Invalid date modifier [{$this->modifier}]");
        }

        return $date;
    }
}
