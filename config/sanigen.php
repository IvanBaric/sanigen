<?php

return [
    'enabled' => true,

    /*
    |--------------------------------------------------------------------------
    | Missing Sanitizer Behavior
    |--------------------------------------------------------------------------
    |
    | Options:
    | - throw: fail fast with InvalidArgumentException
    | - ignore: skip missing sanitizer rules
    | - log: log and skip missing sanitizer rules
    |
    */
    'missing_sanitizer' => 'throw',

    'sanitization_aliases' => [
        'text:plain' => 'strip_html|strip_scripts|strip_emoji|strip_newlines|trim|squish',
        'text:strict' => 'strip_html|strip_scripts|strip_emoji|strip_newlines|ascii|trim|squish',
        'text:title' => 'strip_html|strip_scripts|strip_emoji|strip_newlines|trim|squish|lower|ucfirst',
        'email:clean' => 'trim|lower|email',
        'url:secure' => 'trim|strip_newlines|strip_scripts|url',
        'number:decimal' => 'trim|decimal',
        'phone:clean' => 'trim|phone_clean',
        'text:alpha_dash' => 'trim|lower|alpha_dash',
    ],

    /*
    |--------------------------------------------------------------------------
    | Allowed HTML Tags
    |--------------------------------------------------------------------------
    |
    | Used by strip_tags and strip_scripts.
    |
    */
    'allowed_html_tags' => '<p><b><i><strong><em><ul><ol><li><br><a><h1><h2><h3><h4><h5><h6><table><tr><td><th><thead><tbody><code><pre><blockquote><q><cite><hr><dl><dt><dd>',

    'encoding' => 'UTF-8',

    /*
    |--------------------------------------------------------------------------
    | Strip Scripts Input Guard
    |--------------------------------------------------------------------------
    |
    | Protects from pathological payload sizes before strip_scripts runs.
    |
    */
    'max_strip_scripts_input_length' => 32768,

    /*
    |--------------------------------------------------------------------------
    | Default Rules (lowest priority)
    |--------------------------------------------------------------------------
    |
    | Priority order:
    | 1. model properties ($sanitize / $generate)
    | 2. class-level attributes (#[Sanitize] / #[Generate])
    | 3. config defaults below
    |
    */
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
