<?php

/**
 * Performance Test for Sanigen
 * 
 * This test measures the performance of the sanitization process with a large number of records.
 * It can be configured using environment variables to adjust the test parameters.
 * 
 * Configuration options:
 * - PERFORMANCE_TEST_RECORDS: Number of records to generate (default: 6000)
 * - PERFORMANCE_TEST_BATCH_SIZE: Number of records to insert in each batch (default: 200)
 * - PERFORMANCE_TEST_CHUNK_SIZE: Number of records to process in each chunk during sanitization (default: min(250, totalRecords/2))
 * - PERFORMANCE_TEST_CLEANUP: Whether to clean up the test data after the test (default: false)
 * 
 * MySQL switching options:
 * - PERFORMANCE_TEST_USE_MYSQL: Whether to use MySQL instead of SQLite (default: false)
 * - PERFORMANCE_TEST_SWITCH_AFTER_GENERATION: Whether to switch to MySQL after data generation (default: true)
 *   If true, data is generated in SQLite and then transferred to MySQL for sanitization
 *   If false, MySQL is used from the beginning for both data generation and sanitization
 * 
 * MySQL connection parameters:
 * - DB_MYSQL_HOST: MySQL host (default: 127.0.0.1)
 * - DB_MYSQL_PORT: MySQL port (default: 3306)
 * - DB_MYSQL_DATABASE: MySQL database name (default: sanigen_test)
 * - DB_MYSQL_USERNAME: MySQL username (default: root)
 * - DB_MYSQL_PASSWORD: MySQL password (default: empty)
 * 
 * Example usage:
 * 
 * # Run with SQLite (default)
 * PERFORMANCE_TEST_RECORDS=1000 php vendor/bin/pest tests/Feature/PerformanceTest.php
 * 
 * # Run with MySQL, switching after generation
 * PERFORMANCE_TEST_RECORDS=1000 PERFORMANCE_TEST_USE_MYSQL=true DB_MYSQL_DATABASE=sanigen_test php vendor/bin/pest tests/Feature/PerformanceTest.php
 * 
 * # Run with MySQL from the beginning
 * PERFORMANCE_TEST_RECORDS=1000 PERFORMANCE_TEST_USE_MYSQL=true PERFORMANCE_TEST_SWITCH_AFTER_GENERATION=false DB_MYSQL_DATABASE=sanigen_test php vendor/bin/pest tests/Feature/PerformanceTest.php
 */

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Tests\PerformanceTestModel;
use Illuminate\Support\Facades\Schema;

//beforeEach(function () {
//    if (!Schema::hasTable('performance_test_models')) {
//        Schema::create('performance_test_models', function (Blueprint $table) {
//            $table->id();
//
//            foreach ((new PerformanceTestModel)->getFillable() as $field) {
//                $table->text($field)->nullable();
//            }
//
//            $table->timestamps();
//        });
//    }
//});

test('generate and sanitize records with configurable options', function () {
    // Configuration - these can be adjusted as needed
    $totalRecords = env('PERFORMANCE_TEST_RECORDS', 6000); // Increased default to 500 to verify fix
    $batchSize = env('PERFORMANCE_TEST_BATCH_SIZE', 200);
    
    // Ensure chunk size is smaller than total records to avoid memory issues
    // when processing large datasets
    $defaultChunkSize = min(250, intval($totalRecords / 2));
    $chunkSize = env('PERFORMANCE_TEST_CHUNK_SIZE', $defaultChunkSize);
    
    $cleanupAfterTest = env('PERFORMANCE_TEST_CLEANUP', false);
    
    // Option to switch to MySQL during the test
    $useMysql = env('PERFORMANCE_TEST_USE_MYSQL', true);
    $switchAfterGeneration = env('PERFORMANCE_TEST_SWITCH_AFTER_GENERATION', true);
    
    // Display configuration
    echo "\nPerformance Test Configuration:";
    echo "\n- Total Records: {$totalRecords}";
    echo "\n- Batch Size: {$batchSize}";
    echo "\n- Chunk Size: {$chunkSize}";
    echo "\n- Cleanup After Test: " . ($cleanupAfterTest ? 'Yes' : 'No');
    echo "\n- Use MySQL: " . ($useMysql ? 'Yes' : 'No');
    if ($useMysql) {
        echo "\n- Switch After Generation: " . ($switchAfterGeneration ? 'Yes' : 'No');
    }
    echo "\n";
    
    // Start measuring time
    $startTime = microtime(true);
    $memoryBefore = memory_get_usage();
    
    // Switch to MySQL before generation if configured to do so
    if ($useMysql && !$switchAfterGeneration) {
        echo "\nSwitching to MySQL database before data generation...";
        $mysqlSwitchSuccess = switchToMysql(0); // 0 records to transfer since we're generating directly
        echo " Done.\n";
        
        if (!$mysqlSwitchSuccess) {
            echo "\nFailed to switch to MySQL. Continuing with SQLite.\n";
            $useMysql = false; // Disable MySQL for the rest of the test
        }
    }
    
    // Generate unsanitized data
    generateUnsanitizedRecords($totalRecords, $batchSize);
    
    // Verify records were created
    $recordCount = PerformanceTestModel::count();
    expect($recordCount)->toBe($totalRecords);
    
    // Time taken to generate records
    $generationTime = microtime(true) - $startTime;
    $memoryAfterGeneration = memory_get_usage();
    
    // Switch to MySQL if configured to do so after generation
    if ($useMysql && $switchAfterGeneration) {
        echo "\nSwitching to MySQL database after data generation...";
        $mysqlSwitchSuccess = switchToMysql($totalRecords);
        echo " Done.\n";
        
        if (!$mysqlSwitchSuccess) {
            echo "\nFailed to switch to MySQL. Continuing with SQLite.\n";
            $useMysql = false; // Disable MySQL for the rest of the test
        } else {
            // Verify records were transferred
            $recordCount = PerformanceTestModel::count();
            expect($recordCount)->toBe($totalRecords);
        }
    }
    
    // Start measuring time for sanitization
    $sanitizationStartTime = microtime(true);
    
    // Run the resanitize command
    $output = '';
    // Use the --force option to skip the confirmation prompt
    Artisan::call('sanigen:resanitize', [
        'model' => PerformanceTestModel::class,
        '--chunk' => $chunkSize,
        '--force' => true
    ], $output);
    
    // Time taken for sanitization
    $sanitizationTime = microtime(true) - $sanitizationStartTime;
    $memoryAfterSanitization = memory_get_usage();
    
    // Total time
    $totalTime = microtime(true) - $startTime;
    
    // Output results
    outputResults([
        'Total Records' => $totalRecords,
        'Batch Size' => $batchSize,
        'Chunk Size' => $chunkSize,
        'Generation Time' => round($generationTime, 2) . ' seconds',
        'Sanitization Time' => round($sanitizationTime, 2) . ' seconds',
        'Total Time' => round($totalTime, 2) . ' seconds',
        'Memory Before' => formatBytes($memoryBefore),
        'Memory After Generation' => formatBytes($memoryAfterGeneration),
        'Memory After Sanitization' => formatBytes($memoryAfterSanitization),
        'Memory Increase (Generation)' => formatBytes($memoryAfterGeneration - $memoryBefore),
        'Memory Increase (Sanitization)' => formatBytes($memoryAfterSanitization - $memoryAfterGeneration),
        'Total Memory Increase' => formatBytes($memoryAfterSanitization - $memoryBefore),
    ]);
    
    // Clean up if requested
    if ($cleanupAfterTest) {
        echo "\nCleaning up test data...";
        DB::table('performance_test_models')->truncate();
        echo " Done.\n";
    }
});

/**
 * Generate unsanitized records for performance testing
 */
function generateUnsanitizedRecords(int $totalRecords, int $batchSize): void
{
    $faker = Faker\Factory::create();
    $batches = ceil($totalRecords / $batchSize);
    
    echo "\nGenerating {$totalRecords} unsanitized records in {$batches} batches of {$batchSize}...\n";
    $progressBar = new \Symfony\Component\Console\Helper\ProgressBar(new \Symfony\Component\Console\Output\ConsoleOutput(), $batches);
    $progressBar->start();
    
    for ($batch = 0; $batch < $batches; $batch++) {
        $records = [];
        $recordsToCreate = min($batchSize, $totalRecords - ($batch * $batchSize));
        
        for ($i = 0; $i < $recordsToCreate; $i++) {
            $records[] = generateUnsanitizedRecord($faker);
        }
        
        // Insert batch
        DB::table('performance_test_models')->insert($records);
        $progressBar->advance();
    }
    
    $progressBar->finish();
    echo "\nGeneration completed.\n";
}

/**
 * Generate a single unsanitized record
 */
function generateUnsanitizedRecord($faker): array
{
    return [
        // Text transformation fields - with extra spaces, newlines, etc.
        'trim_field_1' => "  {$faker->sentence()}  ",
        'trim_field_2' => "    {$faker->paragraph()}    ",
        'trim_field_3' => "\t{$faker->words(3, true)}\t",
        'lower_field_1' => strtoupper($faker->sentence()),
        'lower_field_2' => strtoupper($faker->company()),
        'lower_field_3' => strtoupper($faker->catchPhrase()),
        'upper_field_1' => strtolower($faker->sentence()),
        'upper_field_2' => strtolower($faker->company()),
        'upper_field_3' => strtolower($faker->catchPhrase()),
        'ucfirst_field_1' => strtolower($faker->sentence()),
        'ucfirst_field_2' => strtolower($faker->company()),
        'ucfirst_field_3' => strtolower($faker->catchPhrase()),
        'single_space_field_1' => str_replace(' ', '    ', $faker->sentence()),
        'single_space_field_2' => str_replace(' ', '  ', $faker->paragraph()),
        'single_space_field_3' => str_replace(' ', '      ', $faker->words(5, true)),
        'remove_newlines_field_1' => str_replace(' ', "\n", $faker->sentence()),
        'remove_newlines_field_2' => str_replace('. ', ".\n", $faker->paragraph()),
        'remove_newlines_field_3' => str_replace(' ', "\r\n", $faker->words(5, true)),
        
        // Content filtering fields - with special characters, numbers, etc.
        'alpha_only_field_1' => $faker->bothify('???###???###'),
        'alpha_only_field_2' => $faker->bothify('???###???###') . '!@#$%^&*()',
        'alphanumeric_only_field_1' => $faker->bothify('???###???###') . '!@#$%^&*()',
        'alphanumeric_only_field_2' => $faker->bothify('???###???###') . '!@#$%^&*()',
        'alpha_dash_field_1' => $faker->bothify('???###???###') . '!@#$%^&*()',
        'alpha_dash_field_2' => $faker->bothify('???###???###') . '!@#$%^&*()',
        'numeric_only_field_1' => $faker->bothify('???###???###'),
        'numeric_only_field_2' => $faker->bothify('???###???###'),
        'decimal_only_field_1' => $faker->bothify('???###.??###'),
        'decimal_only_field_2' => $faker->bothify('???###.??###'),
        'ascii_only_field_1' => $faker->text() . 'ñáéíóú',
        'ascii_only_field_2' => $faker->text() . 'ñáéíóú',
        'emoji_remove_field_1' => $faker->text() . '👋 🌍 🎉 👨‍👩‍👧‍👦',
        'emoji_remove_field_2' => $faker->text() . '👋 🌍 🎉 👨‍👩‍👧‍👦',
        
        // Security sanitizer fields - with HTML, scripts, etc.
        'strip_tags_field_1' => "<script>alert('XSS')</script><p>{$faker->paragraph()}</p>",
        'strip_tags_field_2' => "<div><h1>{$faker->sentence()}</h1><p>{$faker->paragraph()}</p></div>",
        'no_html_field_1' => "<script>alert('XSS')</script><p>{$faker->paragraph()}</p>",
        'no_html_field_2' => "<div><h1>{$faker->sentence()}</h1><p>{$faker->paragraph()}</p></div>",
        'xss_field_1' => "<script>alert('XSS')</script><img src='x' onerror='alert(1)'><script>eval('alert(\"XSS\")');</script>",
        'xss_field_2' => "<a href='javascript:alert(1)'>{$faker->word()}</a><script>atob('YWxlcnQoIlhTUyIpOw==');</script>",
        'escape_field_1' => "<script>alert('XSS')</script><p>{$faker->paragraph()}</p>",
        'escape_field_2' => "<div><h1>{$faker->sentence()}</h1><p>{$faker->paragraph()}</p></div>",
        'html_special_chars_field_1' => "<script>alert('XSS')</script><p>{$faker->paragraph()}</p>",
        'html_special_chars_field_2' => "<div><h1>{$faker->sentence()}</h1><p>{$faker->paragraph()}</p></div>",
        'json_escape_field_1' => json_encode(['name' => $faker->name(), 'text' => $faker->text()]),
        'json_escape_field_2' => json_encode(['name' => $faker->name(), 'text' => $faker->text()]),
        
        // Format-specific sanitizer fields
        'email_field_1' => strtoupper($faker->email()),
        'email_field_2' => '  ' . strtoupper($faker->email()) . '  ',
        'phone_field_1' => $faker->phoneNumber(),
        'phone_field_2' => $faker->phoneNumber(),
        'url_field_1' => $faker->url(),
        'url_field_2' => str_replace('http://', '', $faker->url()),
        'slug_field_1' => $faker->sentence(),
        'slug_field_2' => $faker->sentence() . ' & ' . $faker->sentence(),
        
        // Combined sanitization fields (using aliases)
        'text_clean_field_1' => "<p>{$faker->paragraph()}</p>\n\n<p>{$faker->paragraph()}</p>",
        'text_clean_field_2' => "<p>{$faker->paragraph()}</p>\n\n<p>{$faker->paragraph()}</p>",
        'text_safe_field_1' => "<script>alert('XSS')</script><p>{$faker->paragraph()}</p>",
        'text_safe_field_2' => "<script>alert('XSS')</script><p>{$faker->paragraph()}</p>",
        'text_secure_field_1' => "<script>alert('XSS')</script><p>{$faker->paragraph()}</p>",
        'text_secure_field_2' => "<script>alert('XSS')</script><p>{$faker->paragraph()}</p>",
        'text_title_field_1' => strtolower($faker->sentence()),
        'text_title_field_2' => strtolower($faker->sentence()),
        'email_clean_field_1' => '  ' . strtoupper($faker->email()) . '  ',
        'email_clean_field_2' => '  ' . strtoupper($faker->email()) . '  ',
        'url_secure_field_1' => "<script>alert('XSS')</script>" . $faker->url(),
        'url_secure_field_2' => "<script>alert('XSS')</script>" . $faker->url(),
        
        'created_at' => now(),
        'updated_at' => now(),
    ];
}

/**
 * Output test results in a formatted table
 */
function outputResults(array $results): void
{
    echo "\n\n=== PERFORMANCE TEST RESULTS ===\n\n";
    
    $maxKeyLength = max(array_map('strlen', array_keys($results)));
    
    foreach ($results as $key => $value) {
        $padding = str_repeat(' ', $maxKeyLength - strlen($key) + 2);
        echo "{$key}:{$padding}{$value}\n";
    }
    
    echo "\n=================================\n";
}

/**
 * Format bytes to human-readable format
 */
function formatBytes($bytes, $precision = 2): string
{
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    
    $bytes /= (1 << (10 * $pow));
    
    return round($bytes, $precision) . ' ' . $units[$pow];
}

/**
 * Switch from SQLite to MySQL database
 * 
 * This function configures a MySQL connection, creates the necessary tables,
 * and transfers data from SQLite to MySQL.
 * 
 * @param int $totalRecords The total number of records to expect
 * @return bool True if successful, false otherwise
 */
function switchToMysql(int $totalRecords): bool
{
    try {
        // Get MySQL connection details from environment variables
        $host = env('DB_MYSQL_HOST', '127.0.0.1');
        $port = env('DB_MYSQL_PORT', '3306');
        $database = env('DB_MYSQL_DATABASE', 'sanigen_test');
        $username = env('DB_MYSQL_USERNAME', 'root');
        $password = env('DB_MYSQL_PASSWORD', '');
        
        // Configure the MySQL connection
        config([
            'database.connections.mysql_test' => [
                'driver' => 'mysql',
                'host' => $host,
                'port' => $port,
                'database' => $database,
                'username' => $username,
                'password' => $password,
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'prefix' => '',
                'strict' => true,
                'engine' => null,
            ],
        ]);
        
        // Connect to MySQL and create the database if it doesn't exist
        $pdo = new \PDO("mysql:host={$host};port={$port}", $username, $password);
        $pdo->exec("CREATE DATABASE IF NOT EXISTS {$database}");
        
        // Switch to the MySQL connection
        DB::purge('mysql_test');
        DB::setDefaultConnection('mysql_test');
        
        // Create the performance_test_models table in MySQL
        if (!Schema::hasTable('performance_test_models')) {
            Schema::create('performance_test_models', function ($table) {
                $table->id();
                
                // Add all the columns from the PerformanceTestModel
                foreach ((new PerformanceTestModel)->getFillable() as $field) {
                    $table->text($field)->nullable();
                }
                
                $table->timestamps();
            });
        } else {
            // If table exists, truncate it to ensure it's empty
            DB::table('performance_test_models')->truncate();
        }
        
        // Transfer data from SQLite to MySQL
        // We'll use the Faker to regenerate the data directly in MySQL
        // This is more efficient than trying to copy data between different database systems
        $faker = Faker\Factory::create();
        $batches = ceil($totalRecords / 200); // Use a batch size of 200
        
        echo "\nTransferring data to MySQL in {$batches} batches...";
        $progressBar = new \Symfony\Component\Console\Helper\ProgressBar(new \Symfony\Component\Console\Output\ConsoleOutput(), $batches);
        $progressBar->start();
        
        for ($batch = 0; $batch < $batches; $batch++) {
            $records = [];
            $recordsToCreate = min(200, $totalRecords - ($batch * 200));
            
            for ($i = 0; $i < $recordsToCreate; $i++) {
                $records[] = generateUnsanitizedRecord($faker);
            }
            
            // Insert batch into MySQL
            DB::table('performance_test_models')->insert($records);
            $progressBar->advance();
        }
        
        $progressBar->finish();
        echo "\nData transfer completed.";
        
        return true;
    } catch (\Exception $e) {
        echo "\nError switching to MySQL: " . $e->getMessage();
        
        // Switch back to SQLite
        DB::setDefaultConnection('testing');
        
        return false;
    }
}