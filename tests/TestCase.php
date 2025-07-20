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
}
