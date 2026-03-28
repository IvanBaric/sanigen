<?php

namespace Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use IvanBaric\Sanigen\SanigenServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    protected function getPackageProviders($app)
    {
        return [SanigenServiceProvider::class];
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->setupGeneratorTestTable();
        $this->setupEnabledConfigTestTable();
        $this->setupSanitizerTestTable();
        $this->setupPerformanceTestTable();
        $this->setupTranslatableTestTable();

        Config::set('sanigen.enabled', true);
    }

    protected function setupGeneratorTestTable(): void
    {
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
            $table->dateTime('carbon_field')->nullable();
            $table->timestamps();
        });
    }

    protected function setupEnabledConfigTestTable(): void
    {
        $this->app['db']->connection()->getSchemaBuilder()->create('test_models', function ($table) {
            $table->id();
            $table->string('title')->nullable();
            $table->string('slug')->nullable();
            $table->string('email')->nullable();
            $table->string('priority_field')->nullable();
            $table->string('attr_only_field')->nullable();
            $table->string('config_only_field')->nullable();
            $table->timestamps();
        });
    }

    protected function setupSanitizerTestTable(): void
    {
        $this->app['db']->connection()->getSchemaBuilder()->create('sanitizer_test_models', function ($table) {
            $table->id();
            $table->string('alpha_dash_field')->nullable();
            $table->string('alnum_field')->nullable();
            $table->string('alpha_field')->nullable();
            $table->string('ascii_field')->nullable();
            $table->string('decimal_field')->nullable();
            $table->string('digits_field')->nullable();
            $table->string('email_field')->nullable();
            $table->string('strip_emoji_field')->nullable();
            $table->string('lower_field')->nullable();
            $table->string('strip_html_field')->nullable();
            $table->string('phone_clean_field')->nullable();
            $table->string('strip_newlines_field')->nullable();
            $table->string('squish_field')->nullable();
            $table->string('slug_field')->nullable();
            $table->string('strip_tags_field')->nullable();
            $table->string('trim_field')->nullable();
            $table->string('ucfirst_field')->nullable();
            $table->string('upper_field')->nullable();
            $table->string('url_field')->nullable();
            $table->string('strip_scripts_field')->nullable();
            $table->string('text_plain_field')->nullable();
            $table->string('text_strict_field')->nullable();
            $table->string('text_title_field')->nullable();
            $table->string('priority_field')->nullable();
            $table->string('attr_only_field')->nullable();
            $table->string('config_only_field')->nullable();
            $table->timestamps();
        });
    }

    protected function setupPerformanceTestTable(): void
    {
        $this->app['db']->connection()->getSchemaBuilder()->create('performance_test_models', function ($table) {
            $table->id();
            $table->text('trim_field')->nullable();
            $table->text('squish_field')->nullable();
            $table->text('strip_scripts_field')->nullable();
            $table->text('text_plain_field')->nullable();
            $table->text('email_field')->nullable();
            $table->timestamps();
        });
    }

    protected function setupTranslatableTestTable(): void
    {
        $this->app['db']->connection()->getSchemaBuilder()->create('translatable_test_models', function ($table) {
            $table->id();
            $table->json('name')->nullable();
            $table->json('description')->nullable();
            $table->timestamps();
        });
    }
}
