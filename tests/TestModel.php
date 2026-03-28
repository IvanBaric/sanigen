<?php

namespace Tests;

use Illuminate\Database\Eloquent\Model;
use IvanBaric\Sanigen\Traits\Sanigen;

class TestModel extends Model
{
    use Sanigen;

    protected $table = 'test_models';

    protected $fillable = ['title', 'slug', 'email'];

    // Define generators
    protected array $generate = [
        'slug' => 'slugify:title',
    ];

    // Define sanitization rules
    protected array $sanitize = [
        'email' => 'trim|lower',
    ];
}
