<?php

namespace Tests;

use Illuminate\Database\Eloquent\Model;
use IvanBaric\Sanigen\Traits\Sanigen;

class PerformanceTestModel extends Model
{
    use Sanigen;
    
    protected $table = 'performance_test_models';
    
    protected $fillable = [
        // Text transformation fields
        'trim_field_1', 'trim_field_2', 'trim_field_3',
        'lower_field_1', 'lower_field_2', 'lower_field_3',
        'upper_field_1', 'upper_field_2', 'upper_field_3',
        'ucfirst_field_1', 'ucfirst_field_2', 'ucfirst_field_3',
        'single_space_field_1', 'single_space_field_2', 'single_space_field_3',
        'remove_newlines_field_1', 'remove_newlines_field_2', 'remove_newlines_field_3',
        
        // Content filtering fields
        'alpha_only_field_1', 'alpha_only_field_2',
        'alphanumeric_only_field_1', 'alphanumeric_only_field_2',
        'alpha_dash_field_1', 'alpha_dash_field_2',
        'numeric_only_field_1', 'numeric_only_field_2',
        'decimal_only_field_1', 'decimal_only_field_2',
        'ascii_only_field_1', 'ascii_only_field_2',
        'emoji_remove_field_1', 'emoji_remove_field_2',
        
        // Security sanitizer fields
        'strip_tags_field_1', 'strip_tags_field_2',
        'no_html_field_1', 'no_html_field_2',
        'xss_field_1', 'xss_field_2',
        'escape_field_1', 'escape_field_2',
        'html_special_chars_field_1', 'html_special_chars_field_2',
        'json_escape_field_1', 'json_escape_field_2',
        
        // Format-specific sanitizer fields
        'email_field_1', 'email_field_2',
        'phone_field_1', 'phone_field_2',
        'url_field_1', 'url_field_2',
        'slug_field_1', 'slug_field_2',
        
        // Combined sanitization fields (using aliases)
        'text_clean_field_1', 'text_clean_field_2',
        'text_safe_field_1', 'text_safe_field_2',
        'text_secure_field_1', 'text_secure_field_2',
        'text_title_field_1', 'text_title_field_2',
        'email_clean_field_1', 'email_clean_field_2',
        'url_secure_field_1', 'url_secure_field_2'
    ];
    
    // Define sanitization rules for each field
    protected $sanitize = [
        // Text transformation rules
        'trim_field_1' => 'trim', 'trim_field_2' => 'trim', 'trim_field_3' => 'trim',
        'lower_field_1' => 'lower', 'lower_field_2' => 'lower', 'lower_field_3' => 'lower',
        'upper_field_1' => 'upper', 'upper_field_2' => 'upper', 'upper_field_3' => 'upper',
        'ucfirst_field_1' => 'ucfirst', 'ucfirst_field_2' => 'ucfirst', 'ucfirst_field_3' => 'ucfirst',
        'single_space_field_1' => 'single_space', 'single_space_field_2' => 'single_space', 'single_space_field_3' => 'single_space',
        'remove_newlines_field_1' => 'remove_newlines', 'remove_newlines_field_2' => 'remove_newlines', 'remove_newlines_field_3' => 'remove_newlines',
        
        // Content filtering rules
        'alpha_only_field_1' => 'alpha_only', 'alpha_only_field_2' => 'alpha_only',
        'alphanumeric_only_field_1' => 'alphanumeric_only', 'alphanumeric_only_field_2' => 'alphanumeric_only',
        'alpha_dash_field_1' => 'alpha_dash', 'alpha_dash_field_2' => 'alpha_dash',
        'numeric_only_field_1' => 'numeric_only', 'numeric_only_field_2' => 'numeric_only',
        'decimal_only_field_1' => 'decimal_only', 'decimal_only_field_2' => 'decimal_only',
        'ascii_only_field_1' => 'ascii_only', 'ascii_only_field_2' => 'ascii_only',
        'emoji_remove_field_1' => 'emoji_remove', 'emoji_remove_field_2' => 'emoji_remove',
        
        // Security sanitizer rules
        'strip_tags_field_1' => 'strip_tags', 'strip_tags_field_2' => 'strip_tags',
        'no_html_field_1' => 'no_html', 'no_html_field_2' => 'no_html',
        'xss_field_1' => 'no_js', 'xss_field_2' => 'no_js',
        'escape_field_1' => 'escape', 'escape_field_2' => 'escape',
        'html_special_chars_field_1' => 'htmlspecialchars', 'html_special_chars_field_2' => 'htmlspecialchars',
        'json_escape_field_1' => 'json_escape', 'json_escape_field_2' => 'json_escape',
        
        // Format-specific sanitizer rules
        'email_field_1' => 'email', 'email_field_2' => 'email',
        'phone_field_1' => 'phone', 'phone_field_2' => 'phone',
        'url_field_1' => 'url', 'url_field_2' => 'url',
        'slug_field_1' => 'slug', 'slug_field_2' => 'slug',
        
        // Combined sanitization rules (using aliases)
        'text_clean_field_1' => 'text:clean', 'text_clean_field_2' => 'text:clean',
        'text_safe_field_1' => 'text:safe', 'text_safe_field_2' => 'text:safe',
        'text_secure_field_1' => 'text:secure', 'text_secure_field_2' => 'text:secure',
        'text_title_field_1' => 'text:title', 'text_title_field_2' => 'text:title',
        'email_clean_field_1' => 'email:clean', 'email_clean_field_2' => 'email:clean',
        'url_secure_field_1' => 'url:secure', 'url_secure_field_2' => 'url:secure'
    ];
}