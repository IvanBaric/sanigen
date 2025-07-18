<?php

namespace {
    /*
    |--------------------------------------------------------------------------
    | Test Case
    |--------------------------------------------------------------------------
    |
    | The closure you provide to your test functions is always bound to a specific PHPUnit test
    | case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
    | need to change it using the "uses()" function to bind a different classes or traits.
    |
    */
    
    use Tests\TestCase;
    
    uses(TestCase::class)
        ->in('Feature')
        ->in('Unit')
        ->in('.');
    
    /*
    |--------------------------------------------------------------------------
    | Expectations
    |--------------------------------------------------------------------------
    |
    | When you're writing tests, you often need to check that values meet certain conditions. The
    | "expect()" function gives you access to a set of "expectations" methods that you can use
    | to assert different things. Of course, you may extend the Expectation API at any time.
    |
    */
    
    expect()->extend('toBeOne', function () {
        return $this->toBe(1);
    });
    
    /*
    |--------------------------------------------------------------------------
    | Functions
    |--------------------------------------------------------------------------
    |
    | While Pest is very powerful out-of-the-box, you may have some testing code specific to your
    | project that you don't want to repeat in every file. Here you can also expose helpers as
    | global functions to help you to reduce the number of lines of code in your test files.
    |
    */
    
    function getPackageProviders($app)
    {
        return [\IvanBaric\Sanigen\SanigenServiceProvider::class];
    }
}

namespace Tests {
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
            'date_offset_field', 'auth_id_field', 'user_property_field'
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
            'unique_code_field' => 'unique_code:10',
            'random_string_field' => 'random_string:12',
            'slug_field' => 'slugify:title',
            'date_offset_field' => 'offset:+7 days',
        ];
    }
    
    // Model for testing auth ID generator
    class AuthIdGeneratorTestModel extends BaseGeneratorTestModel
    {
        protected $generate = [
            'auth_id_field' => 'auth_id',
        ];
    }
    
    // Model for testing user property generator
    class UserPropertyGeneratorTestModel extends BaseGeneratorTestModel
    {
        protected $generate = [
            'user_property_field' => 'user:email',
        ];
    }
}