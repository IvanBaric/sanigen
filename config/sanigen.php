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
        'text:clean'      => 'trim|strip_tags|remove_newlines|single_space',  // Basic text cleaning
        'text:safe'       => 'trim|single_space|xss',                         // Safe text with XSS protection
        'text:secure'     => 'trim|single_space|no_html|strip_tags|xss',      // Secure text with all protections
        'text:strict'     => 'xss|strip_tags|no_html|trim|remove_newlines|single_space', // Maximum security
        'text:no_emoji'   => 'emoji_remove|xss|strip_tags|no_html|trim|remove_newlines|single_space', // No emojis
        'text:alnum'      => 'trim|lower|alphanumeric_only',                  // Letters and numbers only
        'text:alpha'      => 'trim|alpha_only',                               // Letters only
        'ascii:clean'     => 'trim|ascii_only',                               // ASCII characters only

        // Title Formatting
        'text:title'      => 'trim|single_space|no_html|strip_tags|xss|lower|ucfirst', // Clean, safe title with first letter capitalized

        // Identifiers
        'text:identifier' => 'trim|lower|strip_tags',                         // Clean identifier

        // Email Addresses
        'email:clean'     => 'trim|lower|email',                              // Normalized email address

        // URLs
        'url:clean'       => 'trim|remove_newlines|xss',                      // Basic URL cleaning
        'url:secure'      => 'trim|remove_newlines|xss|url',                  // URL with protocol enforcement

        // Numbers
        'number:integer'  => 'trim|numeric_only',                             // Integer values
        'number:decimal'  => 'trim|decimal_only',                             // Decimal values (e.g., "1,234.56€" → "1234.56")

        // Case Transformations
        'case:lower'      => 'trim|lower',                                    // Lowercase text
        'case:upper'      => 'trim|upper',                                    // Uppercase text
        'case:ucfirst'    => 'trim|ucfirst',                                  // First letter capitalized

        // HTML Handling
        'html:escape'     => 'trim|escape',                                   // HTML-escaped text
        'html:entities'   => 'trim|htmlspecialchars',                         // Convert special characters to HTML entities

        // Phone Numbers
        'phone:clean'     => 'trim|phone',                                    // Normalized phone number

        // Slugs
        'slug:clean'      => 'trim|strip_tags|slug',                          // URL-friendly slug

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
    | when using sanitizers like XssSanitizer or StripTagsSanitizer.
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

            // Format for date suffix (used when suffix_type is 'date')
            // Uses PHP date format: https://www.php.net/manual/en/datetime.format.php
            'date_format' => 'd-m-Y',
        ],
    ],

];
