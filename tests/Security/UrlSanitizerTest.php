<?php

use IvanBaric\Sanigen\Sanitizers\UrlSanitizer;

test('url sanitizer accepts valid http and https urls', function (string $input, string $expected) {
    expect((new UrlSanitizer)->apply($input))->toBe($expected);
})->with([
    'https' => ['https://example.com/path', 'https://example.com/path'],
    'http' => ['http://example.com/path', 'http://example.com/path'],
    'without protocol' => ['example.com/path', 'https://example.com/path'],
    'protocol relative' => ['//example.com/path', 'https://example.com/path'],
    'mixed case scheme' => ['HTTPS://example.com/path', 'https://example.com/path'],
]);

test('url sanitizer rejects invalid or disallowed urls', function (string $input) {
    expect((new UrlSanitizer)->apply($input))->toBe('');
})->with([
    'javascript' => ['javascript:alert(1)'],
    'data' => ['data:text/html;base64,PHNjcmlwdD4='],
    'vbscript' => ['vbscript:msgbox("x")'],
    'file' => ['file:///etc/passwd'],
    'gopher' => ['gopher://example.com'],
    'intent' => ['intent://scan/#Intent;scheme=zxing;end'],
    'blob' => ['blob:https://example.com/uuid'],
    'ftp' => ['ftp://example.com/file.txt'],
    'invalid' => ['https://'],
    'empty' => [''],
]);
