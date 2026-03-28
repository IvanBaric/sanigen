<?php

use Illuminate\Support\Facades\Config;
use IvanBaric\Sanigen\Registries\SanitizerRegistry;
use IvanBaric\Sanigen\Sanitizers\Contracts\Sanitizer;
use Tests\SanitizerTestModel;

test('all built in sanitizers resolve from registry', function () {
    $keys = [
        'trim',
        'lower',
        'upper',
        'ucfirst',
        'squish',
        'strip_newlines',
        'strip_html',
        'strip_tags',
        'strip_scripts',
        'strip_emoji',
        'alpha',
        'alnum',
        'alpha_dash',
        'ascii',
        'digits',
        'decimal',
        'email',
        'phone_clean',
        'url',
        'slug',
    ];

    foreach ($keys as $key) {
        expect(SanitizerRegistry::resolve($key))->toBeInstanceOf(Sanitizer::class);
    }
});

test('all configured aliases resolve from registry', function () {
    foreach (array_keys(config('sanigen.sanitization_aliases', [])) as $alias) {
        expect(SanitizerRegistry::resolve($alias))->toBeInstanceOf(Sanitizer::class);
    }
});

test('decimal sanitizer normalizes thousands separators for decimal casts', function (string $input, string $expected) {
    $model = new class extends SanitizerTestModel
    {
        protected array $sanitize = [
            'decimal_field' => 'decimal',
        ];

        protected $casts = [
            'decimal_field' => 'decimal:2',
        ];
    };

    $model->decimal_field = $input;
    $model->save();
    $model->refresh();

    expect($model->getRawOriginal('decimal_field'))->toBe($expected);
    expect($model->decimal_field)->toBe($expected);
})->with([
    ['EUR 1,234.56', '1234.56'],
    ['1.234,56', '1234.56'],
    ['1,234,567.89', '1234567.89'],
]);

test('url secure alias rejects dangerous schemes', function (string $input) {
    $sanitizer = SanitizerRegistry::resolve('url:secure');

    expect($sanitizer->apply($input))->toBe('');
})->with([
    'javascript scheme' => ['javascript:alert(1)'],
    'data scheme' => ['data:text/html;base64,PHNjcmlwdD5hbGVydCgxKTwvc2NyaXB0Pg=='],
    'vbscript scheme' => ['vbscript:msgbox("xss")'],
]);

test('strip scripts sanitizer caps very large payloads', function () {
    Config::set('sanigen.max_strip_scripts_input_length', 128);

    $sanitizer = SanitizerRegistry::resolve('strip_scripts');
    $input = str_repeat('<div>safe</div>', 2000).'<script>alert(1)</script>';
    $result = $sanitizer->apply($input);

    expect($result)->not->toContain('<script>');
    expect(strlen($result))->toBeLessThanOrEqual(128);
});

test('empty sanitized numeric values become null for numeric casts', function () {
    $model = new class extends SanitizerTestModel
    {
        protected array $sanitize = [
            'decimal_field' => 'decimal',
        ];

        protected $casts = [
            'decimal_field' => 'decimal:2',
        ];
    };

    $model->decimal_field = 'N/A';
    $model->save();
    $model->refresh();

    expect($model->getRawOriginal('decimal_field'))->toBeNull();
    expect($model->decimal_field)->toBeNull();
});
