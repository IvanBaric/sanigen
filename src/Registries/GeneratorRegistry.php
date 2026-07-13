<?php

namespace IvanBaric\Sanigen\Registries;

use InvalidArgumentException;
use IvanBaric\Sanigen\Generators\AutoIncrementGenerator;
use IvanBaric\Sanigen\Generators\CarbonGenerator;
use IvanBaric\Sanigen\Generators\Contracts\GeneratorContract;
use IvanBaric\Sanigen\Generators\RandomStringGenerator;
use IvanBaric\Sanigen\Generators\SlugGenerator;
use IvanBaric\Sanigen\Generators\UlidGenerator;
use IvanBaric\Sanigen\Generators\UniqueStringGenerator;
use IvanBaric\Sanigen\Generators\UserPropertyGenerator;
use IvanBaric\Sanigen\Generators\UuidGenerator;

/**
 * Registry for model value generators.
 *
 * This class manages the available generators and resolves them by key.
 */
class GeneratorRegistry
{
    /**
     * Map of generator keys to their class names.
     */
    protected static array $map = [
        'uuid' => UuidGenerator::class,
        'ulid' => UlidGenerator::class,
        'autoincrement' => AutoIncrementGenerator::class,
        'unique_string' => UniqueStringGenerator::class,
        'unique_code' => UniqueStringGenerator::class, // Kept for backward compatibility
        'random_string' => RandomStringGenerator::class,
        'slugify' => SlugGenerator::class,
        'carbon' => CarbonGenerator::class,
        'offset' => CarbonGenerator::class, // Kept for backward compatibility
        'auth_id' => UserPropertyGenerator::class, // Map auth_id to UserPropertyGenerator for backward compatibility
        'user' => UserPropertyGenerator::class,
    ];

    /**
     * Resolve a generator by its key.
     *
     * Supports parameter passing via colon syntax: 'key:parameter'
     * Special handling for 'user:property' to get authenticated user properties.
     *
     * @param  string  $key  The generator key, optionally with a parameter
     * @return GeneratorContract|null The resolved generator or null if not found
     */
    public static function resolve(string $key): ?GeneratorContract
    {
        [$alias, $param] = array_pad(explode(':', $key, 2), 2, null);

        $class = static::$map[$alias] ?? null;

        if (! $class) {
            throw new InvalidArgumentException("Generator with key '{$alias}' does not exist. Check if you have specified the correct generator key.");
        }

        // Always instantiate the class, parameter will be used if provided
        $generator = new $class($param);

        if (! $generator instanceof GeneratorContract) {
            throw new InvalidArgumentException("Generator class [{$class}] must implement ".GeneratorContract::class.'.');
        }

        return $generator;
    }

    /**
     * Register a new generator or override an existing one.
     *
     * @param  string  $key  The key to register the generator under
     * @param  class-string<GeneratorContract>  $class  The fully qualified class name of the generator
     */
    public static function register(string $key, string $class): void
    {
        $key = trim($key);

        if ($key === '') {
            throw new InvalidArgumentException('Generator key cannot be empty.');
        }

        if (! class_exists($class)) {
            throw new InvalidArgumentException("Generator class [{$class}] does not exist.");
        }

        if (! is_subclass_of($class, GeneratorContract::class)) {
            throw new InvalidArgumentException("Generator class [{$class}] must implement ".GeneratorContract::class.'.');
        }

        static::$map[$key] = $class;
    }
}
