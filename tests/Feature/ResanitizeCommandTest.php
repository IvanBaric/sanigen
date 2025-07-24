<?php

use Illuminate\Support\Facades\DB;
use Tests\SanitizerTestModel;

test('resanitize command validates model class', function () {
    // Test with non-existent model class
    $this->artisan('sanigen:resanitize', ['model' => 'NonExistentModel'])
        ->expectsOutput('Model class NonExistentModel does not exist.')
        ->assertExitCode(1);
});

test('resanitize command validates model uses sanitization trait', function () {
    // Create a model class that doesn't use the HasSanitization trait
    class ModelWithoutSanitization extends \Illuminate\Database\Eloquent\Model {}
    
    $this->artisan('sanigen:resanitize', ['model' => ModelWithoutSanitization::class])
        ->expectsOutput('Model ' . ModelWithoutSanitization::class . ' does not use the HasSanitization trait.')
        ->assertExitCode(1);
});

test('resanitize command validates model has sanitization rules', function () {
    // Create a model class that uses the HasSanitization trait but has no sanitization rules
    class ModelWithoutRules extends \Illuminate\Database\Eloquent\Model {
        use \IvanBaric\Sanigen\Traits\HasSanitization;
    }
    
    $this->artisan('sanigen:resanitize', ['model' => ModelWithoutRules::class])
        ->expectsOutput('Model ' . ModelWithoutRules::class . ' does not have any sanitization rules defined.')
        ->assertExitCode(1);
});

test('resanitize command shows backup warning and can be cancelled', function () {
    $this->artisan('sanigen:resanitize', ['model' => SanitizerTestModel::class])
        ->expectsOutput('WARNING: This operation will modify existing records in your database.')
        ->expectsOutput('It is strongly recommended to create a backup of your database before proceeding.')
        ->expectsConfirmation('Do you wish to continue?', 'no')
        ->expectsOutput('Operation cancelled.')
        ->assertExitCode(0);
});

test('resanitize command processes records in chunks', function () {
    // Insert unsanitized data directly into the database to bypass normal sanitization
    DB::table('sanitizer_test_models')->insert([
        ['lower_field' => 'TEST STRING 1', 'created_at' => now(), 'updated_at' => now()],
        ['lower_field' => 'TEST STRING 2', 'created_at' => now(), 'updated_at' => now()],
        ['lower_field' => 'TEST STRING 3', 'created_at' => now(), 'updated_at' => now()],
    ]);
    
    // Run the command with a small chunk size
    $this->artisan('sanigen:resanitize', [
        'model' => SanitizerTestModel::class,
        '--chunk' => 2
    ])
    ->expectsConfirmation('Do you wish to continue?', 'yes')
    ->expectsOutput('Starting resanitization of ' . SanitizerTestModel::class . ' records...')
    ->expectsOutput('Processing in chunks of 2 records.')
    ->assertExitCode(0);
    
    // Verify that all records were sanitized
    $records = SanitizerTestModel::all();
    expect($records)->toHaveCount(3);
    
    foreach ($records as $record) {
        expect($record->lower_field)->toStartWith('test string');
    }
});

test('resanitize command only updates records that need sanitization', function () {
    // Insert a mix of sanitized and unsanitized data
    DB::table('sanitizer_test_models')->insert([
        ['lower_field' => 'TEST STRING', 'created_at' => now(), 'updated_at' => now()],
        ['lower_field' => 'already sanitized', 'created_at' => now(), 'updated_at' => now()],
    ]);
    
    // Run the command
    $this->artisan('sanigen:resanitize', ['model' => SanitizerTestModel::class])
        ->expectsConfirmation('Do you wish to continue?', 'yes')
        ->assertExitCode(0);
    
    // Verify that only the unsanitized record was updated
    $records = SanitizerTestModel::all();
    expect($records)->toHaveCount(2);
    
    $record1 = $records->where('lower_field', 'test string')->first();
    $record2 = $records->where('lower_field', 'already sanitized')->first();
    
    expect($record1)->not->toBeNull();
    expect($record2)->not->toBeNull();
});

test('manually sanitizing a record applies sanitization rules', function () {
    // Insert data that needs multiple sanitization rules
    DB::table('sanitizer_test_models')->insert([
        [
            'trim_field' => '  needs trimming  ',
            'lower_field' => 'NEEDS LOWERCASE',
            'created_at' => now(),
            'updated_at' => now()
        ],
    ]);
    
    // Manually sanitize the record to verify that the sanitization rules work
    $record = SanitizerTestModel::first();
    $record->sanitizeAttributes();
    $record->save();
    
    // Refresh the record from the database
    $record->refresh();
    
    // Verify that all sanitization rules were applied
    expect($record->trim_field)->toBe('needs trimming');
    expect($record->lower_field)->toBe('needs lowercase');
});

test('resanitize command applies sanitization rules', function () {
    // Insert data that needs multiple sanitization rules
    DB::table('sanitizer_test_models')->insert([
        [
            'trim_field' => '  needs trimming  ',
            'lower_field' => 'NEEDS LOWERCASE',
            'created_at' => now(),
            'updated_at' => now()
        ],
    ]);
    
    // Get the record ID for later verification
    $recordId = SanitizerTestModel::first()->id;
    
    // Capture the output of the command
    $outputBuffer = new \Symfony\Component\Console\Output\BufferedOutput;
    
    // Run the command with --force option to skip confirmation
    $exitCode = \Illuminate\Support\Facades\Artisan::call('sanigen:resanitize', [
        'model' => SanitizerTestModel::class,
        '--force' => true,
        '--verbose' => true
    ], $outputBuffer);
    
    // Print the output for debugging
    echo "\nCommand Output:\n" . $outputBuffer->fetch() . "\n";
    
    // Print the exit code
    echo "Exit Code: " . $exitCode . "\n";
    
    // Get the record before sanitization for debugging
    $beforeRecord = SanitizerTestModel::find($recordId);
   # echo "Before Record: " . json_encode($beforeRecord->getAttributes()) . "\n";
    
    // Manually sanitize the record to verify sanitization rules work
    $manualRecord = SanitizerTestModel::find($recordId);
    $manualRecord->sanitizeAttributes();
   # echo "After Manual Sanitization: " . json_encode($manualRecord->getAttributes()) . "\n";
    
    // Get the record again to verify it was sanitized
    $record = SanitizerTestModel::find($recordId);
  #  echo "After Command: " . json_encode($record->getAttributes()) . "\n";
    
    // Verify that all sanitization rules were applied
    expect($record->trim_field)->toBe('needs trimming');
    expect($record->lower_field)->toBe('needs lowercase');
});