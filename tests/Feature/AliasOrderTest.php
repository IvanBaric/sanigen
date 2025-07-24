<?php

use Tests\SanitizerTestModel;
use IvanBaric\Sanigen\Registries\SanitizerRegistry;

test('text:title alias applies filters in correct order', function () {
    // Test the alias pipeline directly using the registry
    $pipeline = config('sanigen.sanitization_aliases.text:title');
    expect($pipeline)->toBe('no_html|no_js|emoji_remove|remove_newlines|trim|single_space|lower|ucfirst');
    
    // Test the actual sanitization result
    $input = '<script>alert("XSS")</script>  HELLO   WORLD  😀  ';
    $sanitizer = SanitizerRegistry::resolve('text:title');
    $result = $sanitizer->apply($input);
    
    // Should remove HTML, JS, emojis, normalize spaces, trim, lowercase, then capitalize first letter
    expect($result)->toBe('Hello world');
});

test('text:secure alias applies filters in correct order', function () {
    // Test the alias pipeline directly using the registry
    $pipeline = config('sanigen.sanitization_aliases.text:secure');
    expect($pipeline)->toBe('no_html|no_js|emoji_remove|trim|single_space');
    
    // Test the actual sanitization result
    $input = '<p><script>alert("XSS")</script>  HELLO   WORLD  😀  </p>';
    $sanitizer = SanitizerRegistry::resolve('text:secure');
    $result = $sanitizer->apply($input);
    
    // Should remove HTML, JS, emojis, trim, and normalize spaces
    expect($result)->toBe('HELLO WORLD');
});

test('text:clean alias applies filters in correct order', function () {
    // Test the alias pipeline directly using the registry
    $pipeline = config('sanigen.sanitization_aliases.text:clean');
    expect($pipeline)->toBe('strip_tags|remove_newlines|trim|single_space');
    
    // Test the actual sanitization result with a simpler input
    $input = '<div>Not allowed</div><p>  Hello   World  </p>';
    $sanitizer = SanitizerRegistry::resolve('text:clean');
    $result = $sanitizer->apply($input);
    
    // Should strip disallowed tags (div), keep allowed tags (p), remove newlines, trim, and normalize spaces
    // Note: <p> is in the allowed_html_tags config, so it won't be stripped
    // Spaces inside HTML tags are preserved
    expect($result)->toBe('Not allowed<p> Hello World </p>');
});