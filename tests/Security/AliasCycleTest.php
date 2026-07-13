<?php

use IvanBaric\Sanigen\Registries\SanitizerRegistry;
use IvanBaric\Sanigen\Sanitizers\Contracts\Sanitizer;

test('direct sanitizer alias cycles are detected', function () {
    config(['sanigen.aliases' => [
        'first' => 'second',
        'second' => 'first',
    ]]);

    expect(fn () => SanitizerRegistry::resolve('first'))
        ->toThrow(InvalidArgumentException::class, 'first -> second -> first');
});

test('indirect sanitizer alias cycles are detected', function () {
    config(['sanigen.aliases' => [
        'first' => 'second',
        'second' => 'third',
        'third' => 'first',
    ]]);

    expect(fn () => SanitizerRegistry::resolve('first'))
        ->toThrow(InvalidArgumentException::class, 'first -> second -> third -> first');
});

test('normal sanitizer aliases resolve and apply', function () {
    config(['sanigen.aliases' => [
        'clean_title' => 'trim|lower|ucfirst',
    ]]);

    $sanitizer = SanitizerRegistry::resolve('clean_title');

    expect($sanitizer)->toBeInstanceOf(Sanitizer::class)
        ->and($sanitizer->apply('  HELLO  '))->toBe('Hello');
});

test('aliases can contain multiple sanitizers without being treated as cycles', function () {
    config(['sanigen.aliases' => [
        'clean_text' => 'strip_html|trim|squish',
    ]]);

    expect(SanitizerRegistry::resolve('clean_text')->apply('<p>Hello   world</p>'))->toBe('Hello world');
});
