<?php

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Tests\BaseGeneratorTestModel;
use Tests\BasicGeneratorTestModel;
use Tests\UserPropertyGeneratorTestModel;

// Model for testing invalid generator key
class InvalidGeneratorTestModel extends BaseGeneratorTestModel
{
    protected $generate = [
        'uuid_field' => 'invalid_generator_key',
    ];
}

test('generates uuid v4 values by default', function () {
    $model = BasicGeneratorTestModel::create([
        'title' => 'Test Title'
    ]);

    // Assert that the UUID was generated and has the correct format
    expect($model->uuid_field)->not->toBeNull();
    expect($model->uuid_field)->toMatch('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i');
});

test('generates uuid v7 values', function () {
    // Register a test model with UUID v7
    class UuidV7TestModel extends \Tests\BaseGeneratorTestModel
    {
        protected $generate = [
            'uuid_field' => 'uuid:v7',
        ];
    }

    $model = UuidV7TestModel::create([
        'title' => 'Test Title'
    ]);

    // Assert that the UUID was generated and has the correct format (v7 starts with timestamp)
    expect($model->uuid_field)->not->toBeNull();
    expect($model->uuid_field)->toMatch('/^[0-9a-f]{8}-[0-9a-f]{4}-7[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i');
});

test('generates uuid v8 values', function () {
    // Register a test model with UUID v8
    class UuidV8TestModel extends \Tests\BaseGeneratorTestModel
    {
        protected $generate = [
            'uuid_field' => 'uuid:v8',
        ];
    }

    $model = UuidV8TestModel::create([
        'title' => 'Test Title'
    ]);

    // Assert that the UUID was generated and has the correct format (v8 is custom format)
    expect($model->uuid_field)->not->toBeNull();
    expect($model->uuid_field)->toMatch('/^[0-9a-f]{8}-[0-9a-f]{4}-8[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i');
});

test('generates ulid values', function () {
    $model = BasicGeneratorTestModel::create([
        'title' => 'Test Title'
    ]);

    // Assert that the ULID was generated and has the correct format (26 characters, alphanumeric)
    expect($model->ulid_field)->not->toBeNull();
    expect($model->ulid_field)->toMatch('/^[0-9A-Z]{26}$/i');
});

test('generates auto increment values', function () {
    // Create multiple models to test auto-increment
    $model1 = BasicGeneratorTestModel::create(['title' => 'Test 1']);
    $model2 = BasicGeneratorTestModel::create(['title' => 'Test 2']);
    $model3 = BasicGeneratorTestModel::create(['title' => 'Test 3']);

    // Assert that the auto-increment values are sequential
    expect($model1->auto_increment_field)->toEqual('1');
    expect($model2->auto_increment_field)->toEqual('2');
    expect($model3->auto_increment_field)->toEqual('3');
});

test('generates unique string values', function () {
    $model = BasicGeneratorTestModel::create([
        'title' => 'Test Title'
    ]);

    // Assert that the unique string was generated with the correct length (10 characters)
    expect($model->unique_code_field)->not->toBeNull();
    expect(strlen($model->unique_code_field))->toBe(10);
    expect($model->unique_code_field)->toMatch('/^[A-Z0-9]{10}$/i');
});

test('generates random string values', function () {
    $model = BasicGeneratorTestModel::create([
        'title' => 'Test Title'
    ]);

    // Assert that the random string was generated with the correct length (12 characters)
    expect($model->random_string_field)->not->toBeNull();
    expect(strlen($model->random_string_field))->toBe(12);
});

test('generates slug values with default increment suffix when needed', function () {
    $model = BasicGeneratorTestModel::create([
        'title' => 'Test Title With Special Characters: & % $ #'
    ]);

    // Assert that the slug was generated from the title
    expect($model->slug_field)->toBe('test-title-with-special-characters');

    // Create another model with the same title to test the increment suffix
    $model2 = BasicGeneratorTestModel::create([
        'title' => 'Test Title With Special Characters: & % $ #'
    ]);

    // Assert that the second slug has an increment suffix
    expect($model2->slug_field)->toBe('test-title-with-special-characters-1');
});

test('generates slug values with date suffix and ensures uniqueness', function () {
    // Register a test model with date suffix
    class SlugDateSuffixTestModel extends \Tests\BaseGeneratorTestModel
    {
        protected $generate = [
            'slug_field' => 'slugify:title,date'
        ];
    }

    // Configure the date format for testing
    config(['sanigen.generator_settings.slugify.suffix_type' => 'date']);
    config(['sanigen.generator_settings.slugify.date_format' => 'Ymd']);

    $model = SlugDateSuffixTestModel::create([
        'title' => 'Test Title'
    ]);

    // Create a second model with the same title to trigger date suffix
    $model2 = SlugDateSuffixTestModel::create([
        'title' => 'Test Title'
    ]);

    // Create a third model with the same title to trigger incremental suffix
    $model3 = SlugDateSuffixTestModel::create([
        'title' => 'Test Title'
    ]);

    // Assert that the slug has a date suffix in the expected format
    $expectedDateSuffix = now()->format('Ymd');
    expect($model->slug_field)->toBe("test-title");
    expect($model2->slug_field)->toBe("test-title-{$expectedDateSuffix}");

    // Assert that the third slug has both date and incremental suffix
    expect($model3->slug_field)->toBe("test-title-{$expectedDateSuffix}-1");
});


test('generates slug values with uuid suffix', function () {
    // Register a test model with uuid suffix
    class SlugUuidSuffixTestModel extends \Tests\BaseGeneratorTestModel
    {
        protected $generate = [
            'slug_field' => 'slugify:title,uuid'
        ];
    }

    // Configure the suffix type for testing
    config(['sanigen.generator_settings.slugify.suffix_type' => 'uuid']);

    $model = SlugUuidSuffixTestModel::create([
        'title' => 'Test Title'
    ]);

    // Create a second model with the same title to trigger suffix
    $model2 = SlugUuidSuffixTestModel::create([
        'title' => 'Test Title'
    ]);

    // Assert that the slug has a uuid suffix
    expect($model2->slug_field)->toMatch('/^test-title-[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i');
});



test('generates user property values', function () {
    // Create a user and authenticate
    $user = new class {
        public function __get($name) {
            if ($name === 'email') {
                return 'test@example.com';
            }
            return null;
        }

        public function __isset($name) {
            return $name === 'email';
        }
    };
    Auth::shouldReceive('user')->andReturn($user);

    $model = UserPropertyGeneratorTestModel::create([
        'title' => 'Test Title'
    ]);

    // Assert that the user property was set to the authenticated user's email
    expect($model->user_property_field)->toBe('test@example.com');
});


test('throws exception for invalid generator key', function () {
    // This should throw an InvalidArgumentException because 'invalid_generator_key' doesn't exist
    expect(function () {
        InvalidGeneratorTestModel::create([
            'title' => 'Test Title'
        ]);
    })->toThrow(\InvalidArgumentException::class, "Generator with key 'invalid_generator_key' does not exist");
});
