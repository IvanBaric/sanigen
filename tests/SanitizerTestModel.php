<?php

namespace Tests;

use Illuminate\Database\Eloquent\Model;
use IvanBaric\Sanigen\Traits\Sanigen;

class SanitizerTestModel extends Model
{
    use Sanigen;
    
    protected $table = 'sanitizer_test_models';
    protected $fillable = [
        'alpha_dash_field',
        'alphanumeric_only_field',
        'alpha_only_field',
        'ascii_only_field',
        'decimal_only_field',
        'email_field',
        'emoji_remove_field',
        'escape_field',
        'html_special_chars_field',
        'json_escape_field',
        'lower_field',
        'no_html_field',
        'numeric_only_field',
        'phone_field',
        'remove_newlines_field',
        'single_space_field',
        'slug_field',
        'strip_tags_field',
        'trim_field',
        'ucfirst_field',
        'upper_field',
        'url_field',
        'xss_field'
    ];
    
    // Define sanitization rules for each field
    protected $sanitize = [
        'alpha_dash_field' => 'alpha_dash',
        'alphanumeric_only_field' => 'alphanumeric_only',
        'alpha_only_field' => 'alpha_only',
        'ascii_only_field' => 'ascii_only',
        'decimal_only_field' => 'decimal_only',
        'email_field' => 'email',
        'emoji_remove_field' => 'emoji_remove',
        'escape_field' => 'escape',
        'html_special_chars_field' => 'htmlspecialchars',
        'json_escape_field' => 'json_escape',
        'lower_field' => 'lower',
        'no_html_field' => 'no_html',
        'numeric_only_field' => 'numeric_only',
        'phone_field' => 'phone',
        'remove_newlines_field' => 'remove_newlines',
        'single_space_field' => 'single_space',
        'slug_field' => 'slug',
        'strip_tags_field' => 'strip_tags',
        'trim_field' => 'trim',
        'ucfirst_field' => 'ucfirst',
        'upper_field' => 'upper',
        'url_field' => 'url',
        'xss_field' => 'no_js'
    ];
}