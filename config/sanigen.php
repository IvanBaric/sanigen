<?php

return [
    'enabled' => true,
    'missing_sanitizer' => 'throw',
    'failure_mode' => 'throw',

    'aliases' => [
        'text' => 'strip_html|strip_emoji|strip_newlines|trim|squish',
        'title' => 'strip_html|strip_emoji|strip_newlines|trim|squish|lower|ucfirst',
        'ascii' => 'strip_html|strip_emoji|strip_newlines|trim|squish|ascii|trim',
        'email' => 'trim|lower|email',
        'url' => 'trim|strip_newlines|url',
        'slug' => 'trim|lower|slug',
        'decimal' => 'trim|decimal',
        'phone' => 'trim|phone_clean',
    ],

    'allowed_html_tags' => '<p><b><i><strong><em><ul><ol><li><br><a><h1><h2><h3><h4><h5><h6><table><tr><td><th><thead><tbody><code><pre><blockquote><q><cite><hr><dl><dt><dd>',
    'safe_html_allowed_schemes' => ['http', 'https', 'mailto'],
    'allowed_url_schemes' => ['http', 'https'],
    'default_url_scheme' => 'https',
    'encoding' => 'UTF-8',
    'max_html_input_length' => 32768,
    'max_strip_scripts_input_length' => 32768,
    'max_scalar_input_length' => 65535,
    'max_nested_items' => 500,
    'max_nested_depth' => 8,
    'resanitize_max_chunk' => 1000,
    'tenant_column' => 'team_id',
    'resanitize_require_tenant_scope' => true,
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
