<?php

use Illuminate\Support\Facades\Config;
use Tests\SanitizerTestModel;

test('sanitizes values when enabled', function () {
    // Ensure the package is enabled
    Config::set('sanigen.enabled', true);
    
    // Create a model with sanitizers
    $model = new SanitizerTestModel();
    $model->lower_field = 'TEST STRING';
    $model->trim_field = '   Test with spaces   ';
    $model->save();
    
    // Assert that the values were sanitized
    expect($model->lower_field)->toBe('test string');
    expect($model->trim_field)->toBe('Test with spaces');
});

test('does not sanitize values when disabled', function () {
    // Disable the package
    Config::set('sanigen.enabled', false);
    
    // Create a model with sanitizers
    $model = new SanitizerTestModel();
    $model->lower_field = 'TEST STRING';
    $model->trim_field = '   Test with spaces   ';
    $model->save();
    
    // Assert that the values were not sanitized
    expect($model->lower_field)->toBe('TEST STRING');
    expect($model->trim_field)->toBe('   Test with spaces   ');
});

test('does not sanitize on update when disabled', function () {
    // First create with enabled to have initial values
    Config::set('sanigen.enabled', true);
    $model = new SanitizerTestModel();
    $model->lower_field = 'test string';
    $model->trim_field = 'test with spaces';
    $model->save();
    
    // Now disable and update
    Config::set('sanigen.enabled', false);
    
    $model->lower_field = 'UPDATED STRING';
    $model->trim_field = '   Updated with spaces   ';
    $model->save();
    
    // Assert that the values were not sanitized on update
    expect($model->lower_field)->toBe('UPDATED STRING');
    expect($model->trim_field)->toBe('   Updated with spaces   ');
});

test('sanitizes on update when enabled', function () {
    // Ensure the package is enabled
    Config::set('sanigen.enabled', true);
    
    $model = new SanitizerTestModel();
    $model->lower_field = 'test string';
    $model->trim_field = 'test with spaces';
    $model->save();
    
    $model->lower_field = 'UPDATED STRING';
    $model->trim_field = '   Updated with spaces   ';
    $model->save();
    
    // Assert that the values were sanitized on update
    expect($model->lower_field)->toBe('updated string');
    expect($model->trim_field)->toBe('Updated with spaces');
});