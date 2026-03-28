<?php

use IvanBaric\Sanigen\Registries\SanitizerRegistry;

test('text:title alias keeps canonical order', function () {
    $pipeline = config('sanigen.sanitization_aliases.text:title');
    expect($pipeline)->toBe('strip_html|strip_scripts|strip_emoji|strip_newlines|trim|squish|lower|ucfirst');

    $input = "<script>alert(\"XSS\")</script>  HELLO   WORLD  \u{1F600}  ";
    $result = SanitizerRegistry::resolve('text:title')->apply($input);

    expect($result)->toBe('Hello world');
});

test('text:plain alias performs plain normalization cleanup', function () {
    $pipeline = config('sanigen.sanitization_aliases.text:plain');
    expect($pipeline)->toBe('strip_html|strip_scripts|strip_emoji|strip_newlines|trim|squish');

    $input = "<p><script>alert(\"XSS\")</script>  HELLO   WORLD  \u{1F600}  </p>";
    $result = SanitizerRegistry::resolve('text:plain')->apply($input);

    expect($result)->toBe('HELLO WORLD');
});

test('text:strict alias applies stronger cleanup', function () {
    $pipeline = config('sanigen.sanitization_aliases.text:strict');
    expect($pipeline)->toBe('strip_html|strip_scripts|strip_emoji|strip_newlines|ascii|trim|squish');

    $input = "<p>H\u{00E9}llo \u{1F44B}   \u{017D}</p>";
    $result = SanitizerRegistry::resolve('text:strict')->apply($input);

    expect($result)->toBe('Hllo');
});
