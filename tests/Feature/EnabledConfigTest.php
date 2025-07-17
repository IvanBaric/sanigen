<?php

use Illuminate\Support\Facades\Config;
use Tests\TestModel;

test('generates values when enabled', function () {
    // Ensure the package is enabled
    Config::set('sanigen.enabled', true);
    
    // Create a model with generators
    $model = TestModel::create([
        'title' => 'Test Title',
        'email' => ' TEST@example.com '
    ]);
    
    // Assert that the slug was generated
    expect($model->slug)->toBe('test-title');
    
    // Assert that the email was sanitized
    expect($model->email)->toBe('test@example.com');
});

test('does not generate values when disabled', function () {
    // Disable the package
    Config::set('sanigen.enabled', false);
    
    // Create a model with generators
    $model = TestModel::create([
        'title' => 'Test Title',
        'email' => ' TEST@example.com '
    ]);
    
    // Assert that the slug was not generated
    expect($model->slug)->toBeNull();
    
    // Assert that the email was not sanitized
    expect($model->email)->toBe(' TEST@example.com ');
});

test('does not sanitize on update when disabled', function () {
    // First create with enabled to have initial values
    Config::set('sanigen.enabled', true);
    $model = TestModel::create([
        'title' => 'Test Title',
        'email' => 'test@example.com'
    ]);
    
    // Now disable and update
    Config::set('sanigen.enabled', false);
    
    $model->email = ' UPDATED@example.com ';
    $model->save();
    
    // Refresh from database
    $model->refresh();
    
    // Assert that the email was not sanitized on update
    expect($model->email)->toBe(' UPDATED@example.com ');
});

test('sanitizes on update when enabled', function () {
    // Ensure the package is enabled
    Config::set('sanigen.enabled', true);
    
    $model = TestModel::create([
        'title' => 'Test Title',
        'email' => 'test@example.com'
    ]);
    
    $model->email = ' UPDATED@example.com ';
    $model->save();
    
    // Refresh from database
    $model->refresh();
    
    // Assert that the email was sanitized on update
    expect($model->email)->toBe('updated@example.com');
});