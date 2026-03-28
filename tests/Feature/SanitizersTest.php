<?php

use IvanBaric\Sanigen\Registries\SanitizerRegistry;
use Tests\SanitizerTestModel;

test('renamed sanitizer rules are applied through model property api', function () {
    $model = new SanitizerTestModel;

    $model->alpha_dash_field = 'Test-123 @#$';
    $model->alnum_field = 'Test-123 @#$';
    $model->alpha_field = 'Test-123 @#$';
    $model->ascii_field = "cafe \u{017E}";
    $model->decimal_field = 'EUR 1,234.56';
    $model->digits_field = 'A1B2-3';
    $model->email_field = ' USER@EXAMPLE.COM ';
    $model->strip_emoji_field = "Hello \u{1F44B} World \u{1F30D}";
    $model->lower_field = 'HELLO';
    $model->strip_html_field = '<p>Hello <b>World</b></p>';
    $model->phone_clean_field = '+1 (123) 456-7890 ext. 123';
    $model->strip_newlines_field = "Line 1\nLine 2\r\nLine 3";
    $model->squish_field = 'Hello    world';
    $model->slug_field = 'Hello World';
    $model->strip_tags_field = '<p>Hello <strong>world</strong></p>';
    $model->trim_field = '  Hello  ';
    $model->ucfirst_field = 'hello';
    $model->upper_field = 'hello';
    $model->url_field = 'example.com/path';
    $model->strip_scripts_field = '<script>alert(1)</script><img src="x" onerror="alert(1)">safe';

    $model->save();

    expect($model->alpha_dash_field)->toBe('Test-123');
    expect($model->alnum_field)->toBe('Test123');
    expect($model->alpha_field)->toBe('Test');
    expect($model->ascii_field)->toBe('cafe ');
    expect($model->decimal_field)->toBe('1234.56');
    expect($model->digits_field)->toBe('123');
    expect($model->email_field)->toBe('user@example.com');
    expect($model->strip_emoji_field)->toBe('Hello  World ');
    expect($model->lower_field)->toBe('hello');
    expect($model->strip_html_field)->toBe('Hello World');
    expect($model->phone_clean_field)->toBe('+11234567890123');
    expect($model->strip_newlines_field)->toBe('Line 1Line 2Line 3');
    expect($model->squish_field)->toBe('Hello world');
    expect($model->slug_field)->toBe('hello-world');
    expect($model->strip_tags_field)->toBe('<p>Hello <strong>world</strong></p>');
    expect($model->trim_field)->toBe('Hello');
    expect($model->ucfirst_field)->toBe('Hello');
    expect($model->upper_field)->toBe('HELLO');
    expect($model->url_field)->toBe('https://example.com/path');
    expect($model->strip_scripts_field)->toBe('safe');
});

test('configured aliases apply the expected pipelines', function () {
    $model = new SanitizerTestModel;
    $model->text_field = "<p>Hello \u{1F44B}</p><script>alert(1)</script>   World";
    $model->ascii_field = "<p>Caf\u{00E9} \u{1F44B}</p><script>alert(1)</script>   \u{017D}";
    $model->title_field = "<script>alert(1)</script>  HELLO   WORLD  \u{1F600}";
    $model->save();

    expect($model->text_field)->toBe('Hello World');
    expect($model->ascii_field)->toBe('Caf');
    expect($model->title_field)->toBe('Hello world');
});

test('strip_scripts removes suspicious javascript patterns', function () {
    $model = new SanitizerTestModel;
    $model->strip_scripts_field = 'before fetch("https://evil.test") document.cookie = "x=1"; alert("XSS") after';
    $model->save();

    expect($model->strip_scripts_field)->toBe('before after');
});

test('removed encoding sanitizers are no longer available', function () {
    expect(fn () => SanitizerRegistry::resolve('htmlspecialchars'))
        ->toThrow(InvalidArgumentException::class, "Sanitizer 'htmlspecialchars' does not exist.");

    expect(fn () => SanitizerRegistry::resolve('json_escape'))
        ->toThrow(InvalidArgumentException::class, "Sanitizer 'json_escape' does not exist.");
});

test('missing sanitizer throw mode still fails fast by default', function () {
    $model = new class extends SanitizerTestModel
    {
        protected array $sanitize = [
            'strip_scripts_field' => 'definitely_missing',
        ];
    };

    expect(function () use ($model) {
        $model->strip_scripts_field = 'test';
        $model->save();
    })->toThrow(InvalidArgumentException::class, "Sanitizer 'definitely_missing' does not exist.");
});
