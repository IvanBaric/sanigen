<?php

use IvanBaric\Sanigen\Sanitizers\SafeHtmlSanitizer;

test('safe html keeps basic markup and hardens links', function () {
    $html = (new SafeHtmlSanitizer)->apply('<p>Hello <strong>world</strong> <a href="http://example.com">Link</a></p>');

    expect($html)
        ->toContain('<p>')
        ->toContain('<strong>world</strong>')
        ->toContain('href="https://example.com"')
        ->toContain('rel="noopener noreferrer"');
});

test('safe html removes dangerous elements attributes and protocols', function (string $payload) {
    $html = (new SafeHtmlSanitizer)->apply($payload);

    expect(strtolower($html))
        ->not->toContain('<script')
        ->not->toContain('<style')
        ->not->toContain('<iframe')
        ->not->toContain('<object')
        ->not->toContain('<embed')
        ->not->toContain('<svg')
        ->not->toContain('javascript:')
        ->not->toContain('onclick')
        ->not->toContain('onerror')
        ->not->toContain('onmouseover');

    expect($html === '' || str_contains($html, 'Link') || str_contains($html, 'Tekst'))->toBeTrue();
})->with([
    'script tag' => ['<script>alert(1)</script>Link'],
    'javascript href' => ['<a href="javascript:alert(1)">Link</a>'],
    'malformed event link' => ['<a/onmouseover=confirm(document.domain)>Link</a>'],
    'image onerror' => ['<img src="x" onerror="alert(1)">Tekst'],
    'svg onload' => ['<svg onload="alert(1)"></svg>Tekst'],
    'paragraph onclick' => ['<p onclick="alert(1)">Tekst</p>'],
]);
