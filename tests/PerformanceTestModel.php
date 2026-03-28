<?php

namespace Tests;

use Illuminate\Database\Eloquent\Model;
use IvanBaric\Sanigen\Traits\Sanigen;

class PerformanceTestModel extends Model
{
    use Sanigen;

    protected $table = 'performance_test_models';

    protected $fillable = [
        'trim_field',
        'squish_field',
        'strip_scripts_field',
        'text_plain_field',
        'email_field',
    ];

    protected array $sanitize = [
        'trim_field' => 'trim',
        'squish_field' => 'squish',
        'strip_scripts_field' => 'strip_scripts',
        'text_plain_field' => 'text:plain',
        'email_field' => 'email:clean',
    ];
}
