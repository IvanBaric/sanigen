<?php

use Tests\TranslatableTestModel;

test('sanitizes script patterns in translatable fields', function () {
    $model = new TranslatableTestModel;
    $model->setTranslation('name', 'hr', '<script>alert("xss")</script>smart');

    $model->sanitizeAttributes();

    expect($model->getTranslation('name', 'hr'))->toBe('smart');
});

test('sanitizes all locales for translatable field arrays', function () {
    $model = new TranslatableTestModel;

    $model->name = [
        'hr' => '<script>alert("xss")</script>smart',
        'en' => 'This is <script>alert("xss")</script> safe text',
    ];

    $model->sanitizeAttributes();

    expect($model->getTranslation('name', 'hr'))->toBe('smart');
    expect($model->getTranslation('name', 'en'))->toBe('This is safe text');
});

test('strip_scripts keeps allowed html while removing suspicious patterns', function () {
    config(['sanigen.allowed_html_tags' => '<p><strong><em>']);

    $model = new TranslatableTestModel;
    $model->setTranslation(
        'description',
        'en',
        '<p>This is <strong>important</strong> and <em>emphasized</em> text with '.
        '<script>alert("xss")</script> and <img src="x" onerror="alert(\'XSS\')"></p>'
    );

    $model->sanitizeAttributes();

    $sanitized = $model->getTranslation('description', 'en');

    expect($sanitized)->toContain('<p>');
    expect($sanitized)->toContain('<strong>important</strong>');
    expect($sanitized)->toContain('<em>emphasized</em>');
    expect($sanitized)->not->toContain('<script>');
    expect($sanitized)->not->toContain('onerror=');
});
