<?php

use IvanBaric\Sanigen\Exceptions\SanitizationException;
use IvanBaric\Sanigen\Registries\SanitizerRegistry;
use IvanBaric\Sanigen\Sanitizers\Contracts\Sanitizer;
use Tests\SanitizerTestModel;

final class ThrowingSecuritySanitizer implements Sanitizer
{
    public function apply(string $value): string
    {
        throw new RuntimeException('Synthetic sanitizer failure.');
    }
}

test('sanitization failure throws by default and does not save original payload', function () {
    SanitizerRegistry::register('throwing_security_test', ThrowingSecuritySanitizer::class);

    $model = new class extends SanitizerTestModel
    {
        protected array $sanitize = [
            'strip_scripts_field' => 'throwing_security_test',
        ];
    };

    expect(function () use ($model) {
        $model->strip_scripts_field = '<script>alert(1)</script>';
        $model->save();
    })->toThrow(SanitizationException::class);

    expect(SanitizerTestModel::query()->where('strip_scripts_field', '<script>alert(1)</script>')->exists())->toBeFalse();
});

test('invalid failure mode fails closed instead of falling back silently', function () {
    config(['sanigen.failure_mode' => 'unknown']);
    SanitizerRegistry::register('throwing_security_test_invalid_mode', ThrowingSecuritySanitizer::class);

    $model = new class extends SanitizerTestModel
    {
        protected array $sanitize = [
            'strip_scripts_field' => 'throwing_security_test_invalid_mode',
        ];
    };

    expect(fn () => $model->forceFill(['strip_scripts_field' => 'value'])->save())
        ->toThrow(InvalidArgumentException::class, 'Invalid Sanigen failure mode');
});
