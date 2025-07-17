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
    protected $generate = [
        'slug' => 'slugify:title',
    ];
    
    // Define sanitization rules
    protected $sanitize = [
        'email' => 'trim|lower',
    ];
}