<?php

use IvanBaric\Sanigen\Generators\Contracts\GeneratorContract;
use IvanBaric\Sanigen\Registries\GeneratorRegistry;
use IvanBaric\Sanigen\Registries\SanitizerRegistry;
use IvanBaric\Sanigen\Sanitizers\Contracts\Sanitizer;

final class RegistrySecuritySanitizer implements Sanitizer
{
    public function apply(string $value): string
    {
        return $value;
    }
}

final class RegistrySecurityGenerator implements GeneratorContract
{
    public function __construct(?string $param = null) {}

    public function generate(string $field, object $model): mixed
    {
        return 'generated';
    }
}

final class RegistrySecurityWrongClass {}

test('sanitizer registry validates custom registrations', function () {
    SanitizerRegistry::register('registry_security_sanitizer', RegistrySecuritySanitizer::class);

    expect(SanitizerRegistry::resolve('registry_security_sanitizer'))->toBeInstanceOf(RegistrySecuritySanitizer::class);
    expect(fn () => SanitizerRegistry::register('', RegistrySecuritySanitizer::class))->toThrow(InvalidArgumentException::class);
    expect(fn () => SanitizerRegistry::register('missing_class', 'Missing\\Sanitizer'))->toThrow(InvalidArgumentException::class);
    expect(fn () => SanitizerRegistry::register('wrong_class', RegistrySecurityWrongClass::class))->toThrow(InvalidArgumentException::class);
});

test('generator registry validates custom registrations without limiting user property generator', function () {
    GeneratorRegistry::register('registry_security_generator', RegistrySecurityGenerator::class);

    expect(GeneratorRegistry::resolve('registry_security_generator'))->toBeInstanceOf(RegistrySecurityGenerator::class);
    expect(GeneratorRegistry::resolve('user:email'))->toBeInstanceOf(GeneratorContract::class);
    expect(fn () => GeneratorRegistry::register('', RegistrySecurityGenerator::class))->toThrow(InvalidArgumentException::class);
    expect(fn () => GeneratorRegistry::register('missing_class', 'Missing\\Generator'))->toThrow(InvalidArgumentException::class);
    expect(fn () => GeneratorRegistry::register('wrong_class', RegistrySecurityWrongClass::class))->toThrow(InvalidArgumentException::class);
});
