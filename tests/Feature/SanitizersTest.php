<?php

use Tests\SanitizerTestModel;

test('sanitizes alpha dash values', function () {
    $model = new SanitizerTestModel();
    $model->alpha_dash_field = 'Test-123 @#$%^&*()';
    $model->save();
    
    // Assert that non-alphanumeric characters (except dashes and underscores) are removed
    expect($model->alpha_dash_field)->toBe('Test-123');
});

test('sanitizes alphanumeric only values', function () {
    $model = new SanitizerTestModel();
    $model->alphanumeric_only_field = 'Test-123 @#$%^&*()';
    $model->save();
    
    // Assert that only alphanumeric characters remain
    expect($model->alphanumeric_only_field)->toBe('Test123');
});

test('sanitizes alpha only values', function () {
    $model = new SanitizerTestModel();
    $model->alpha_only_field = 'Test-123 @#$%^&*()';
    $model->save();
    
    // Assert that only alphabetic characters remain
    expect($model->alpha_only_field)->toBe('Test');
});

test('sanitizes ascii only values', function () {
    $model = new SanitizerTestModel();
    $model->ascii_only_field = 'Test-123 @#$%^&*() ñáéíóú';
    $model->save();
    
    // Assert that only ASCII characters remain
    expect($model->ascii_only_field)->toBe('Test-123 @#$%^&*() ');
});

test('sanitizes decimal only values', function () {
    $model = new SanitizerTestModel();
    $model->decimal_only_field = 'Test 123.45 @#$%^&*()';
    $model->save();
    
    // Assert that only decimal numbers remain
    expect($model->decimal_only_field)->toBe('123.45');
});

test('sanitizes email values', function () {
    $model = new SanitizerTestModel();
    $model->email_field = ' TEST@example.com ';
    $model->save();
    
    // Assert that email is properly formatted
    expect($model->email_field)->toBe('test@example.com');
});

test('sanitizes emoji remove values', function () {
    $model = new SanitizerTestModel();
    $model->emoji_remove_field = 'Hello 👋 World 🌍';
    $model->save();
    
    // Assert that emojis are removed
    expect($model->emoji_remove_field)->toBe('Hello  World ');
});

test('sanitizes escape values', function () {
    $model = new SanitizerTestModel();
    $model->escape_field = '<script>alert("XSS")</script>';
    $model->save();
    
    // Assert that HTML is escaped
    expect($model->escape_field)->toBe('&amp;lt;script&amp;gt;alert(&amp;quot;XSS&amp;quot;)&amp;lt;/script&amp;gt;');
});

test('sanitizes html special chars values', function () {
    $model = new SanitizerTestModel();
    $model->html_special_chars_field = '<script>alert("XSS")</script>';
    $model->save();
    
    // Assert that HTML special characters are converted
    expect($model->html_special_chars_field)->toBe('<script>alert("XSS")</script>');
});

test('sanitizes json escape values', function () {
    $model = new SanitizerTestModel();
    $model->json_escape_field = '"Test" with "quotes"';
    $model->save();
    
    // Assert that quotes are escaped for JSON
    expect($model->json_escape_field)->not->toBe('"Test" with "quotes"');
    expect($model->json_escape_field)->toContain('Test');
    expect($model->json_escape_field)->toContain('with');
    expect($model->json_escape_field)->toContain('quotes');
});

test('sanitizes lower values', function () {
    $model = new SanitizerTestModel();
    $model->lower_field = 'TEST STRING';
    $model->save();
    
    // Assert that text is converted to lowercase
    expect($model->lower_field)->toBe('test string');
});

test('sanitizes no html values', function () {
    $model = new SanitizerTestModel();
    $model->no_html_field = '<p>Test <strong>with</strong> <em>HTML</em></p>';
    $model->save();
    
    // Assert that HTML tags are removed
    expect($model->no_html_field)->toBe('Test with HTML');
});

test('sanitizes numeric only values', function () {
    $model = new SanitizerTestModel();
    $model->numeric_only_field = 'Test 123.45 @#$%^&*()';
    $model->save();
    
    // Assert that only numeric characters remain
    expect($model->numeric_only_field)->toBe('12345');
});

test('sanitizes phone values', function () {
    $model = new SanitizerTestModel();
    $model->phone_field = '+1 (123) 456-7890 ext. 123';
    $model->save();
    
    // Assert that phone number is properly formatted
    expect($model->phone_field)->toBe('+11234567890123');
});

test('sanitizes remove newlines values', function () {
    $model = new SanitizerTestModel();
    $model->remove_newlines_field = "Line 1\nLine 2\r\nLine 3";
    $model->save();
    
    // Assert that newlines are removed
    expect($model->remove_newlines_field)->toBe('Line 1Line 2Line 3');
});

test('sanitizes single space values', function () {
    $model = new SanitizerTestModel();
    $model->single_space_field = 'Multiple    spaces    between    words';
    $model->save();
    
    // Assert that multiple spaces are replaced with single spaces
    expect($model->single_space_field)->toBe('Multiple spaces between words');
});

test('sanitizes slug values', function () {
    $model = new SanitizerTestModel();
    $model->slug_field = 'Test String With Spaces and Special Characters: @#$%^&*()';
    $model->save();
    
    // Assert that text is converted to a slug
    expect($model->slug_field)->toBe('test-string-with-spaces-and-special-characters');
});

test('sanitizes strip tags values', function () {
    $model = new SanitizerTestModel();
    $model->strip_tags_field = '<p>Test <strong>with</strong> <em>HTML</em></p>';
    $model->save();
    
    // Assert that HTML tags are removed
    expect($model->strip_tags_field)->toBe('<p>Test <strong>with</strong> <em>HTML</em></p>');
});

test('sanitizes trim values', function () {
    $model = new SanitizerTestModel();
    $model->trim_field = '   Test with spaces   ';
    $model->save();
    
    // Assert that leading and trailing spaces are removed
    expect($model->trim_field)->toBe('Test with spaces');
});

test('sanitizes ucfirst values', function () {
    $model = new SanitizerTestModel();
    $model->ucfirst_field = 'test string';
    $model->save();
    
    // Assert that first character is capitalized
    expect($model->ucfirst_field)->toBe('Test string');
});

test('sanitizes upper values', function () {
    $model = new SanitizerTestModel();
    $model->upper_field = 'test string';
    $model->save();
    
    // Assert that text is converted to uppercase
    expect($model->upper_field)->toBe('TEST STRING');
});

test('sanitizes url values', function () {
    $model = new SanitizerTestModel();
    $model->url_field = 'http://example.com/path with spaces';
    $model->save();
    
    // Assert that URL is properly formatted
    expect($model->url_field)->toBe('http://example.com/path with spaces');
});

test('sanitizes xss values', function () {
    $model = new SanitizerTestModel();
    $model->xss_field = '<script>alert("XSS")</script><img src="x" onerror="alert(\'XSS\')">';
    $model->save();
    
    // Assert that XSS attacks are neutralized
    expect($model->xss_field)->not->toContain('<script>');
    expect($model->xss_field)->not->toContain('onerror=');
});