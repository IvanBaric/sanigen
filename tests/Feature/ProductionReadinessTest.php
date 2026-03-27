<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use IvanBaric\Sanigen\Registries\SanitizerRegistry;
use IvanBaric\Sanigen\Sanitizers\Contracts\Sanitizer;
use Tests\SanitizerTestModel;

test('all built in sanitizers resolve from the registry', function () {
    $keys = [
        'alpha_dash',
        'alphanumeric_only',
        'alpha_only',
        'ascii_only',
        'decimal_only',
        'email',
        'emoji_remove',
        'escape',
        'htmlspecialchars',
        'json_escape',
        'lower',
        'no_html',
        'no_js',
        'numeric_only',
        'phone',
        'remove_newlines',
        'single_space',
        'slug',
        'strip_tags',
        'trim',
        'ucfirst',
        'upper',
        'url',
    ];

    foreach ($keys as $key) {
        expect(SanitizerRegistry::resolve($key))->toBeInstanceOf(Sanitizer::class);
    }
});

test('all configured aliases resolve from the registry', function () {
    foreach (array_keys(config('sanigen.sanitization_aliases', [])) as $alias) {
        expect(SanitizerRegistry::resolve($alias))->toBeInstanceOf(Sanitizer::class);
    }
});

test('decimal sanitizer normalizes thousands separators for decimal casts', function (string $input, string $expected) {
    $model = new class extends SanitizerTestModel {
        protected $casts = [
            'decimal_only_field' => 'decimal:2',
        ];
    };

    $model->decimal_only_field = $input;
    $model->save();
    $model->refresh();

    expect($model->getRawOriginal('decimal_only_field'))->toBe($expected);
    expect($model->decimal_only_field)->toBe($expected);
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

test('url sanitizer preserves safe schemes and normalizes protocol relative urls', function () {
    $sanitizer = SanitizerRegistry::resolve('url');

    expect($sanitizer->apply('https://example.com'))->toBe('https://example.com');
    expect($sanitizer->apply('ftp://example.com/file.txt'))->toBe('ftp://example.com/file.txt');
    expect($sanitizer->apply('//example.com/path'))->toBe('https://example.com/path');
    expect($sanitizer->apply('example.com/path'))->toBe('https://example.com/path');
});

test('missing sanitizer throw mode still fails fast by default', function () {
    $model = new class extends SanitizerTestModel {
        protected $sanitize = [
            'trim_field' => 'definitely_missing',
        ];
    };

    expect(function () use ($model) {
        $model->trim_field = '  value  ';
        $model->save();
    })->toThrow(InvalidArgumentException::class, "Sanitizer 'definitely_missing' does not exist.");
});

test('missing sanitizer ignore mode skips the bad rule without crashing', function () {
    Config::set('sanigen.missing_sanitizer', 'ignore');

    $model = new class extends SanitizerTestModel {
        protected $sanitize = [
            'trim_field' => 'definitely_missing',
        ];
    };

    $model->trim_field = '  value  ';
    $model->save();

    expect($model->trim_field)->toBe('  value  ');
});

test('missing sanitizer log mode logs and skips the bad rule', function () {
    Log::spy();
    Config::set('sanigen.missing_sanitizer', 'log');

    $model = new class extends SanitizerTestModel {
        protected $sanitize = [
            'trim_field' => 'definitely_missing',
        ];
    };

    $model->trim_field = '  value  ';
    $model->save();

    expect($model->trim_field)->toBe('  value  ');
    Log::shouldHaveReceived('error')->once()->with("Sanigen: missing sanitizer 'definitely_missing'.");
});

test('mass update still sanitizes values before persistence', function () {
    $model = SanitizerTestModel::create([
        'email_field' => 'first@example.com',
    ]);

    $model->update([
        'email_field' => ' UPDATED@EXAMPLE.COM ',
    ]);

    expect($model->fresh()->email_field)->toBe('updated@example.com');
});

test('no js sanitizer caps very large payloads to avoid pathological input', function () {
    Config::set('sanigen.max_xss_input_length', 128);

    $sanitizer = SanitizerRegistry::resolve('no_js');
    $input = str_repeat('<div>safe</div>', 2000) . '<script>alert(1)</script>';
    $result = $sanitizer->apply($input);

    expect($result)->not->toContain('<script>');
    expect(strlen($result))->toBeLessThanOrEqual(128);
});

test('sanitize attributes recovers malformed decimal strings from raw database writes', function () {
    $modelClass = new class extends SanitizerTestModel {
        protected $casts = [
            'decimal_only_field' => 'decimal:2',
        ];
    };

    DB::table('sanitizer_test_models')->insert([
        'decimal_only_field' => '1.234.56',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $record = $modelClass::query()->firstOrFail();

    expect($record->sanitizeAttributes())->toBeTrue();
    $record->saveQuietly();
    $record->refresh();

    expect($record->getRawOriginal('decimal_only_field'))->toBe('1234.56');
    expect($record->decimal_only_field)->toBe('1234.56');
});

test('empty sanitized numeric values become null for numeric casts', function () {
    $model = new class extends SanitizerTestModel {
        protected $casts = [
            'decimal_only_field' => 'decimal:2',
        ];
    };

    $model->decimal_only_field = 'N/A';
    $model->save();
    $model->refresh();

    expect($model->getRawOriginal('decimal_only_field'))->toBeNull();
    expect($model->decimal_only_field)->toBeNull();
});
