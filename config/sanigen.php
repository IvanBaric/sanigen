<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Package Status
    |--------------------------------------------------------------------------
    |
    | This option controls whether the Sanigen package is enabled.
    | When disabled, no automatic sanitization or generation will occur.
    |
    */
    'enabled' => true,

    /*
    |--------------------------------------------------------------------------
    | Missing Sanitizer Behavior
    |--------------------------------------------------------------------------
    |
    | Controls what happens when a model references a sanitizer key that does
    | not exist (e.g. a typo in $sanitize or a removed sanitizer).
    |
    | Options:
    | - 'throw'  : throw InvalidArgumentException (fail fast, default)
    | - 'ignore' : skip the missing sanitizer and keep the current value
    | - 'log'    : log an error (if logger() exists) and skip the sanitizer
    |
    */
    'missing_sanitizer' => 'throw',

    /*
    |--------------------------------------------------------------------------
    | Sanitization Aliases
    |--------------------------------------------------------------------------
    |
    | These aliases define predefined groups of sanitization rules that can be
    | used in your models. Each alias is a named pipeline of sanitizers that
    | will be applied in sequence.
    |
    | Example usage in a model:
    |
    | protected $sanitize = [
    |     'title' => 'text:title',
    |     'content' => 'text:safe',
    |     'email' => 'email:clean'
    | ];
    |
    */
    'sanitization_aliases' => [
        // Text Processing
        'text:title'      => 'no_html|no_js|emoji_remove|remove_newlines|trim|single_space|lower|ucfirst', // Clean, safe title with first letter capitalized
        'text:clean'      => 'strip_tags|remove_newlines|trim|single_space',  // Basic text cleaning
        'text:secure'     => 'no_html|no_js|emoji_remove|trim|single_space',      // Secure text with all protections

        // Email Addresses
        'email:clean'     => 'trim|lower|email',                              // Normalized email address

        // URLs
        'url:clean'       => 'trim|remove_newlines|no_js',                      // Basic URL cleaning
        'url:secure'      => 'trim|remove_newlines|no_js|url',                  // URL with protocol enforcement

        // Numbers
        'number:integer'  => 'trim|numeric_only',                             // Integer values
        'number:decimal'  => 'trim|decimal_only',                             // Decimal values (e.g., "1,234.56€" → "1234.56")

        // Phone Numbers
        'phone:clean'     => 'trim|phone',                                    // Normalized phone number

        // Special Character Sets
        'text:alpha_dash' => 'trim|lower|alpha_dash',                         // Letters, numbers, hyphens, underscores

        // JSON
        'json:escape'     => 'trim|json_escape',                              // JSON-safe string


    //  You can add your own custom sanitization aliases here.
    //  Custom aliases allow you to create reusable sanitization
    //  pipelines specific to your application's needs.
    //  Example:

    //  'my_custom_alias_1' => 'trim|lower|strip_tags',



    ],

    /*
    |--------------------------------------------------------------------------
    | Allowed HTML Tags
    |--------------------------------------------------------------------------
    |
    | This list defines which HTML tags are allowed to remain in sanitized content
    | when using sanitizers like NoJsSanitizer or StripTagsSanitizer.
    |
    | All other HTML tags will be removed, providing a basic level of safety
    | while still allowing some formatting.
    |
    */
    'allowed_html_tags' => '<p><b><i><strong><em><ul><ol><li><br><a><h1><h2><h3><h4><h5><h6><table><tr><td><th><thead><tbody><code><pre><blockquote><q><cite><hr><dl><dt><dd>',

    /*
    |--------------------------------------------------------------------------
    | Default Encoding
    |--------------------------------------------------------------------------
    |
    | This setting defines the default character encoding used by all sanitizers
    | that perform encoding-specific operations, such as htmlspecialchars.
    |
    */
    'encoding' => 'UTF-8',

    /*
    |--------------------------------------------------------------------------
    | Maximum XSS Input Length
    |--------------------------------------------------------------------------
    |
    | To avoid pathological inputs consuming excessive CPU or memory, the
    | no_js sanitizer will truncate extremely large payloads before applying
    | its sanitization pipeline.
    |
    */
    'max_xss_input_length' => 32768,
    

    /*
    |--------------------------------------------------------------------------
    | Generator Settings
    |--------------------------------------------------------------------------
    |
    | This section contains settings for various generators used by the package.
    | You can customize the behavior of each generator according to your needs.
    |
    */
    'generator_settings' => [
        // Slug Generator Settings
        'slugify' => [
            // Type of suffix to use for ensuring uniqueness
            // Options: 'increment', 'date', 'uuid'
            'suffix_type' => 'increment',

            // Regenerate slug fields on update when the source field changes.
            // Default is false to keep public URLs stable.
            'slug_updates_on_save' => false,

            // Format for date suffix (used when suffix_type is 'date')
            // Uses PHP date format: https://www.php.net/manual/en/datetime.format.php
            'date_format' => 'd-m-Y',
        ],
    ],

];
