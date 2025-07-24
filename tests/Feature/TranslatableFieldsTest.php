<?php

use Tests\TranslatableTestModel;

test('sanitizes XSS in translatable fields', function () {
    $model = new TranslatableTestModel();
    
    // Set translatable field with XSS content in Croatian language
    $model->setTranslation('name', 'hr', '<script>alert("xss")</script>smart');
    
    // Manually trigger sanitization without saving to database
    $model->sanitizeAttributes();
    
    // Assert that script tags are removed but the text remains
    expect($model->getTranslation('name', 'hr'))->toBe('smart');
});

test('sanitizes XSS in translatable fields with multiple translations', function () {
    $model = new TranslatableTestModel();
    
    // Set translatable field with XSS content in multiple languages
    $model->setTranslation('name', 'hr', '<script>alert("xss")</script>smart');
    $model->setTranslation('name', 'en', 'This is <script>alert("xss")</script> safe text');
    
    // Manually trigger sanitization without saving to database
    $model->sanitizeAttributes();
    
    // Assert that script tags are removed but the text remains in all translations
    expect($model->getTranslation('name', 'hr'))->toBe('smart');
    expect($model->getTranslation('name', 'en'))->toBe('This is safe text');
});

test('sanitizes XSS in translatable fields when setting all translations at once', function () {
    $model = new TranslatableTestModel();
    
    // Set all translations at once with an array
    $model->name = [
        'hr' => '<script>alert("xss")</script>smart',
        'en' => 'This is <script>alert("xss")</script> safe text'
    ];
    
    // Manually trigger sanitization without saving to database
    $model->sanitizeAttributes();
    
    // Assert that script tags are removed but the text remains in all translations
    expect($model->getTranslation('name', 'hr'))->toBe('smart');
    expect($model->getTranslation('name', 'en'))->toBe('This is safe text');
});

test('sanitizes complex XSS attacks in translatable fields', function () {
    $model = new TranslatableTestModel();
    
    // Set translatable field with more complex XSS attacks
    $model->setTranslation('description', 'hr', '<img src="x" onerror="alert(\'XSS\')">' . 
                                               '<a href="javascript:alert(\'XSS\')">Click me</a>' .
                                               'Some text alert("XSS") more text');
    
    // Manually trigger sanitization without saving to database
    $model->sanitizeAttributes();
    
    // Assert that all XSS vectors are removed
    $sanitized = $model->getTranslation('description', 'hr');
    expect($sanitized)->not->toContain('onerror=');
    expect($sanitized)->not->toContain('javascript:');
    expect($sanitized)->not->toContain('alert(');
    
    // The sanitizer may remove all content if it's deemed unsafe,
    // so we don't make assumptions about what text is preserved
    // The important thing is that the XSS vectors are removed
});

test('sanitizes translatable fields while preserving allowed HTML', function () {
    // Temporarily configure allowed HTML tags
    config(['sanigen.allowed_html_tags' => '<p><strong><em>']);
    
    $model = new TranslatableTestModel();
    
    // Set translatable field with mixed content
    $model->setTranslation('description', 'en', 
        '<p>This is <strong>important</strong> and <em>emphasized</em> text with ' .
        '<script>alert("xss")</script> and <img src="x" onerror="alert(\'XSS\')">' .
        '</p>');
    
    // Manually trigger sanitization without saving to database
    $model->sanitizeAttributes();
    
    // Assert that dangerous tags are removed but allowed tags remain
    $sanitized = $model->getTranslation('description', 'en');
    expect($sanitized)->toContain('<p>');
    expect($sanitized)->toContain('<strong>important</strong>');
    expect($sanitized)->toContain('<em>emphasized</em>');
    expect($sanitized)->not->toContain('<script>');
    expect($sanitized)->not->toContain('onerror=');
    
    // Reset config to default
    config(['sanigen.allowed_html_tags' => '']);
});