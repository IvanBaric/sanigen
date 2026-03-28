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
        'alnum_field',
        'alpha_field',
        'ascii_field',
        'decimal_field',
        'digits_field',
        'email_field',
        'strip_emoji_field',
        'lower_field',
        'strip_html_field',
        'phone_clean_field',
        'strip_newlines_field',
        'squish_field',
        'slug_field',
        'strip_tags_field',
        'trim_field',
        'ucfirst_field',
        'upper_field',
        'url_field',
        'strip_scripts_field',
        'text_plain_field',
        'text_strict_field',
        'text_title_field',
        'priority_field',
        'attr_only_field',
        'config_only_field',
    ];

    protected array $sanitize = [
        'alpha_dash_field' => 'alpha_dash',
        'alnum_field' => 'alnum',
        'alpha_field' => 'alpha',
        'ascii_field' => 'ascii',
        'decimal_field' => 'decimal',
        'digits_field' => 'digits',
        'email_field' => 'email',
        'strip_emoji_field' => 'strip_emoji',
        'lower_field' => 'lower',
        'strip_html_field' => 'strip_html',
        'phone_clean_field' => 'phone_clean',
        'strip_newlines_field' => 'strip_newlines',
        'squish_field' => 'squish',
        'slug_field' => 'slug',
        'strip_tags_field' => 'strip_tags',
        'trim_field' => 'trim',
        'ucfirst_field' => 'ucfirst',
        'upper_field' => 'upper',
        'url_field' => 'url',
        'strip_scripts_field' => 'strip_scripts',
        'text_plain_field' => 'text:plain',
        'text_strict_field' => 'text:strict',
        'text_title_field' => 'text:title',
    ];
}
