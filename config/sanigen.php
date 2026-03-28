<?php

return [
    'enabled' => true,
    'missing_sanitizer' => 'throw',

    'aliases' => [
        'text' => 'strip_html|strip_scripts|strip_emoji|strip_newlines|trim|squish',
        'title' => 'strip_html|strip_scripts|strip_emoji|strip_newlines|trim|squish|lower|ucfirst',
        'ascii' => 'strip_html|strip_scripts|strip_emoji|strip_newlines|trim|squish|ascii',
        'email' => 'trim|lower|email',
        'url' => 'trim|strip_newlines|strip_scripts|url',
        'slug' => 'trim|lower|slug',
        'decimal' => 'trim|decimal',
        'phone' => 'trim|phone_clean',
    ],

    'allowed_html_tags' => '<p><b><i><strong><em><ul><ol><li><br><a><h1><h2><h3><h4><h5><h6><table><tr><td><th><thead><tbody><code><pre><blockquote><q><cite><hr><dl><dt><dd>',
    'encoding' => 'UTF-8',
    'max_strip_scripts_input_length' => 32768,
    'sanitize_defaults' => [],
    'generate_defaults' => [],

    'generator_settings' => [
        'slugify' => [
            'suffix_type' => 'increment',
            'slug_updates_on_save' => false,
            'date_format' => 'd-m-Y',
        ],
    ],
];
