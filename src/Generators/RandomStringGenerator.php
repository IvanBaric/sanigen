<?php

namespace IvanBaric\Sanigen\Generators;

use Illuminate\Support\Str;
use IvanBaric\Sanigen\Generators\Contracts\GeneratorContract;

/**
 * Generates random strings of a specified length.
 * 
 * This generator creates random strings using Laravel's Str::random() helper,
 * which produces strings containing letters and numbers.
 */
class RandomStringGenerator implements GeneratorContract
{
    /**
     * Create a new random string generator.
     *
     * @param int $length The length of the random string to generate
     */
    public function __construct(protected int $length = 8) {}

    /**
     * Generate a random string of the specified length.
     *
     * @param string $field The field name that will store the random string
     * @param object $model The model instance to generate the value for
     * @return string The generated random string
     */
    public function generate(string $field, object $model): string
    {
        return Str::random($this->length);
    }
}
