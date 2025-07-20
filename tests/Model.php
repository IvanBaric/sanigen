<?php

namespace Tests;

use Illuminate\Database\Eloquent\Model;
use IvanBaric\Sanigen\Traits\HasGenerators;

// Base test model with common properties
class BaseGeneratorTestModel extends Model
{
    use HasGenerators;

    protected $table = 'generator_test_models';
    protected $fillable = [
        'title', 'uuid_field', 'ulid_field', 'auto_increment_field', 
        'unique_code_field', 'random_string_field', 'slug_field', 
        'user_property_field'
    ];
}

// Model for testing basic generators (no user-related generators)
class BasicGeneratorTestModel extends BaseGeneratorTestModel
{
    // Define generators for basic fields only
    protected $generate = [
        'uuid_field' => 'uuid',
        'ulid_field' => 'ulid',
        'auto_increment_field' => 'autoincrement',
        'unique_code_field' => 'unique_string:10',
        'random_string_field' => 'random_string:12',
        'slug_field' => 'slugify:title',
    ];
}


// Model for testing user property generator
class UserPropertyGeneratorTestModel extends BaseGeneratorTestModel
{
    protected $generate = [
        'user_property_field' => 'user:email',
    ];
}
