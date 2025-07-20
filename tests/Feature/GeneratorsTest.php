<?php

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Tests\BaseGeneratorTestModel;
use Tests\BasicGeneratorTestModel;
use Tests\AuthIdGeneratorTestModel;
use Tests\UserPropertyGeneratorTestModel;
use Tests\CarbonGeneratorTestModel;

// Model for testing invalid generator key
class InvalidGeneratorTestModel extends BaseGeneratorTestModel
{
    protected $generate = [
        'uuid_field' => 'invalid_generator_key',
    ];
}

test('generates uuid values', function () {
    $model = BasicGeneratorTestModel::create([
        'title' => 'Test Title'
    ]);

    // Assert that the UUID was generated and has the correct format
    expect($model->uuid_field)->not->toBeNull();
    expect($model->uuid_field)->toMatch('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i');
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

test('generates slug values', function () {
    $model = BasicGeneratorTestModel::create([
        'title' => 'Test Title With Special Characters: & % $ #'
    ]);

    // Assert that the slug was generated from the title
    expect($model->slug_field)->toBe('test-title-with-special-characters');
});

test('generates date offset values', function () {
    $model = BasicGeneratorTestModel::create([
        'title' => 'Test Title'
    ]);

    // Assert that the date was generated with the correct offset (approximately 7 days in the future)
    expect($model->date_offset_field)->not->toBeNull();

    $expectedDate = now()->addDays(7)->startOfMinute();
    $actualDate = $model->date_offset_field;

    // Allow a small difference (1 minute) to account for test execution time
    expect($expectedDate->diffInMinutes($actualDate))->toBeLessThanOrEqual(1);
});

test('generates auth id values', function () {
    // Create a user and authenticate
    $user = new class extends Model {
        protected $table = 'users';
    };
    $user->id = 123;
    Auth::shouldReceive('id')->andReturn(123);

    $model = AuthIdGeneratorTestModel::create([
        'title' => 'Test Title'
    ]);

    // Assert that the auth ID was set to the authenticated user's ID
    expect($model->auth_id_field)->toBe(123);
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

test('generates carbon date values', function () {
    $model = CarbonGeneratorTestModel::create([
        'title' => 'Test Title'
    ]);

    // Assert that the date was generated with the correct offset (approximately 14 days in the future)
    expect($model->date_offset_field)->not->toBeNull();

    $expectedDate = now()->addDays(14)->startOfMinute();
    $actualDate = $model->date_offset_field;

    // Allow a small difference (1 minute) to account for test execution time
    expect($expectedDate->diffInMinutes($actualDate))->toBeLessThanOrEqual(1);
});

test('throws exception for invalid generator key', function () {
    // This should throw an InvalidArgumentException because 'invalid_generator_key' doesn't exist
    expect(function () {
        InvalidGeneratorTestModel::create([
            'title' => 'Test Title'
        ]);
    })->toThrow(\InvalidArgumentException::class, "Generator with key 'invalid_generator_key' does not exist");
});
