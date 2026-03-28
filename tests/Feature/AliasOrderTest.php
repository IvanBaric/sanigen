<?php

use IvanBaric\Sanigen\Registries\SanitizerRegistry;

test('title alias keeps canonical order', function () {
    $pipeline = config('sanigen.aliases.title');
    expect($pipeline)->toBe('strip_html|strip_scripts|strip_emoji|strip_newlines|trim|squish|lower|ucfirst');

    $input = "<script>alert(\"XSS\")</script>  HELLO   WORLD  \u{1F600}  ";
    $result = SanitizerRegistry::resolve('title')->apply($input);

    expect($result)->toBe('Hello world');
});

test('text alias performs generic text cleanup', function () {
    $pipeline = config('sanigen.aliases.text');
    expect($pipeline)->toBe('strip_html|strip_scripts|strip_emoji|strip_newlines|trim|squish');

    $input = "<p><script>alert(\"XSS\")</script>  HELLO   WORLD  \u{1F600}  </p>";
    $result = SanitizerRegistry::resolve('text')->apply($input);

    expect($result)->toBe('HELLO WORLD');
});

test('ascii alias applies ascii-only cleanup', function () {
    $pipeline = config('sanigen.aliases.ascii');
    expect($pipeline)->toBe('strip_html|strip_scripts|strip_emoji|strip_newlines|trim|squish|ascii');

    $input = "<p>H\u{00E9}llo \u{1F44B}   \u{017D}</p>";
    $result = SanitizerRegistry::resolve('ascii')->apply($input);

    expect($result)->toBe('Hllo');
});
