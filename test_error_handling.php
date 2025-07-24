<?php

require_once __DIR__ . '/vendor/autoload.php';

use IvanBaric\Sanigen\Registries\SanitizerRegistry;

echo "Testing error handling changes...\n\n";

// Test 1: Direct registry call with non-existent sanitizer
echo "Test 1: Direct registry call with non-existent sanitizer\n";
try {
    $sanitizer = SanitizerRegistry::resolve('non_existent_sanitizer');
    echo "❌ FAILED: Expected exception was not thrown\n";
} catch (InvalidArgumentException $e) {
    echo "✅ PASSED: Exception thrown as expected: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "❌ FAILED: Unexpected exception type: " . get_class($e) . " - " . $e->getMessage() . "\n";
}

echo "\n";

// Test 2: Valid sanitizer should still work
echo "Test 2: Valid sanitizer should still work\n";
try {
    $sanitizer = SanitizerRegistry::resolve('trim');
    if ($sanitizer !== null) {
        echo "✅ PASSED: Valid sanitizer resolved successfully\n";
    } else {
        echo "❌ FAILED: Valid sanitizer returned null\n";
    }
} catch (Exception $e) {
    echo "❌ FAILED: Unexpected exception for valid sanitizer: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 3: Pipeline with non-existent sanitizer
echo "Test 3: Pipeline with non-existent sanitizer in alias\n";
try {
    // This would simulate a config alias with a non-existent sanitizer
    // We can't easily test this without setting up the full Laravel environment
    echo "⚠️  SKIPPED: Pipeline test requires full Laravel environment\n";
} catch (Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
}

echo "\nAll direct tests completed!\n";