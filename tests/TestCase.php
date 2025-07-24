<?php

namespace Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    protected function getPackageProviders($app)
    {
        return [\IvanBaric\Sanigen\SanigenServiceProvider::class];
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->setupGeneratorTestTable();
        $this->setupEnabledConfigTestTable();
        $this->setupSanitizerTestTable();
        $this->setupPerformanceTestTable();
        $this->setupTranslatableTestTable();

        // Ensure the package is enabled
        Config::set('sanigen.enabled', true);
    }

    /**
     * Set up the table for generator tests
     */
    protected function setupGeneratorTestTable(): void
    {
        // Create a test table with columns for testing all generators
        $this->app['db']->connection()->getSchemaBuilder()->create('generator_test_models', function ($table) {
            $table->id();
            $table->string('uuid_field')->nullable();
            $table->string('ulid_field')->nullable();
            $table->string('auto_increment_field')->nullable();
            $table->string('unique_code_field')->nullable();
            $table->string('random_string_field')->nullable();
            $table->string('title')->nullable();
            $table->string('slug_field')->nullable();
            $table->string('user_property_field')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Set up the table for enabled config tests
     */
    protected function setupEnabledConfigTestTable(): void
    {
        // Create a test table for enabled config tests
        $this->app['db']->connection()->getSchemaBuilder()->create('test_models', function ($table) {
            $table->id();
            $table->string('title')->nullable();
            $table->string('slug')->nullable();
            $table->string('email')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Set up the table for sanitizer tests
     */
    protected function setupSanitizerTestTable(): void
    {
        // Create a test table for sanitizer tests
        $this->app['db']->connection()->getSchemaBuilder()->create('sanitizer_test_models', function ($table) {
            $table->id();
            $table->string('alpha_dash_field')->nullable();
            $table->string('alphanumeric_only_field')->nullable();
            $table->string('alpha_only_field')->nullable();
            $table->string('ascii_only_field')->nullable();
            $table->string('decimal_only_field')->nullable();
            $table->string('email_field')->nullable();
            $table->string('emoji_remove_field')->nullable();
            $table->string('escape_field')->nullable();
            $table->string('html_special_chars_field')->nullable();
            $table->string('json_escape_field')->nullable();
            $table->string('lower_field')->nullable();
            $table->string('no_html_field')->nullable();
            $table->string('numeric_only_field')->nullable();
            $table->string('phone_field')->nullable();
            $table->string('remove_newlines_field')->nullable();
            $table->string('single_space_field')->nullable();
            $table->string('slug_field')->nullable();
            $table->string('strip_tags_field')->nullable();
            $table->string('trim_field')->nullable();
            $table->string('ucfirst_field')->nullable();
            $table->string('upper_field')->nullable();
            $table->string('url_field')->nullable();
            $table->string('xss_field')->nullable();
            $table->timestamps();
        });
    }
    
    /**
     * Set up the table for performance tests
     */
    protected function setupPerformanceTestTable(): void
    {
        // Create a test table for performance tests with many columns
        $this->app['db']->connection()->getSchemaBuilder()->create('performance_test_models', function ($table) {
            $table->id();
            
            // Text transformation fields
            $table->text('trim_field_1')->nullable();
            $table->text('trim_field_2')->nullable();
            $table->text('trim_field_3')->nullable();
            $table->text('lower_field_1')->nullable();
            $table->text('lower_field_2')->nullable();
            $table->text('lower_field_3')->nullable();
            $table->text('upper_field_1')->nullable();
            $table->text('upper_field_2')->nullable();
            $table->text('upper_field_3')->nullable();
            $table->text('ucfirst_field_1')->nullable();
            $table->text('ucfirst_field_2')->nullable();
            $table->text('ucfirst_field_3')->nullable();
            $table->text('single_space_field_1')->nullable();
            $table->text('single_space_field_2')->nullable();
            $table->text('single_space_field_3')->nullable();
            $table->text('remove_newlines_field_1')->nullable();
            $table->text('remove_newlines_field_2')->nullable();
            $table->text('remove_newlines_field_3')->nullable();
            
            // Content filtering fields
            $table->text('alpha_only_field_1')->nullable();
            $table->text('alpha_only_field_2')->nullable();
            $table->text('alphanumeric_only_field_1')->nullable();
            $table->text('alphanumeric_only_field_2')->nullable();
            $table->text('alpha_dash_field_1')->nullable();
            $table->text('alpha_dash_field_2')->nullable();
            $table->text('numeric_only_field_1')->nullable();
            $table->text('numeric_only_field_2')->nullable();
            $table->text('decimal_only_field_1')->nullable();
            $table->text('decimal_only_field_2')->nullable();
            $table->text('ascii_only_field_1')->nullable();
            $table->text('ascii_only_field_2')->nullable();
            $table->text('emoji_remove_field_1')->nullable();
            $table->text('emoji_remove_field_2')->nullable();
            
            // Security sanitizer fields
            $table->text('strip_tags_field_1')->nullable();
            $table->text('strip_tags_field_2')->nullable();
            $table->text('no_html_field_1')->nullable();
            $table->text('no_html_field_2')->nullable();
            $table->text('xss_field_1')->nullable();
            $table->text('xss_field_2')->nullable();
            $table->text('escape_field_1')->nullable();
            $table->text('escape_field_2')->nullable();
            $table->text('html_special_chars_field_1')->nullable();
            $table->text('html_special_chars_field_2')->nullable();
            $table->text('json_escape_field_1')->nullable();
            $table->text('json_escape_field_2')->nullable();
            
            // Format-specific sanitizer fields
            $table->text('email_field_1')->nullable();
            $table->text('email_field_2')->nullable();
            $table->text('phone_field_1')->nullable();
            $table->text('phone_field_2')->nullable();
            $table->text('url_field_1')->nullable();
            $table->text('url_field_2')->nullable();
            $table->text('slug_field_1')->nullable();
            $table->text('slug_field_2')->nullable();
            
            // Combined sanitization fields (using aliases)
            $table->text('text_clean_field_1')->nullable();
            $table->text('text_clean_field_2')->nullable();
            $table->text('text_safe_field_1')->nullable();
            $table->text('text_safe_field_2')->nullable();
            $table->text('text_secure_field_1')->nullable();
            $table->text('text_secure_field_2')->nullable();
            $table->text('text_title_field_1')->nullable();
            $table->text('text_title_field_2')->nullable();
            $table->text('email_clean_field_1')->nullable();
            $table->text('email_clean_field_2')->nullable();
            $table->text('url_secure_field_1')->nullable();
            $table->text('url_secure_field_2')->nullable();
            
            $table->timestamps();
        });
    }
    
    /**
     * Set up the table for translatable field tests
     */
    protected function setupTranslatableTestTable(): void
    {
        // Create a test table for translatable fields
        $this->app['db']->connection()->getSchemaBuilder()->create('translatable_test_models', function ($table) {
            $table->id();
            $table->json('name')->nullable();
            $table->json('description')->nullable();
            $table->timestamps();
        });
    }
}
