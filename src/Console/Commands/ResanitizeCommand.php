<?php

namespace IvanBaric\Sanigen\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use IvanBaric\Sanigen\Registries\SanitizerRegistry;
use IvanBaric\Sanigen\Traits\HasSanitization;

class ResanitizeCommand extends Command
{
    /**
     * The name and signature of the command.
     *
     * @var string
     */
    protected $signature = 'sanigen:resanitize
                            {model : The model class name (e.g., "App\\Models\\Post")}
                            {--chunk=200 : The number of records to process at once}
                            {--force : Skip confirmation prompt}';

    /**
     * The command description.
     *
     * @var string
     */
    protected $description = 'Re-apply sanitization rules to existing model records';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        try {
            $modelClass = $this->argument('model');
            $chunkSize = (int) $this->option('chunk');

            // Validate the model class
            if (!class_exists($modelClass)) {
                $this->error("Model class {$modelClass} does not exist.");
                return Command::FAILURE;
            }

            // Create an instance to check if it uses the HasSanitization trait
            $model = new $modelClass();

            if (!$this->usesSanitization($model)) {
                $this->error("Model {$modelClass} does not use the HasSanitization trait.");
                return Command::FAILURE;
            }

            // Check if the model has sanitization rules defined
            $hasSanitizationRules = false;

            // Use reflection to check for the sanitize property and its value
            $reflection = new \ReflectionClass($model);

            // Check if the property exists in the class or its parents
            if ($reflection->hasProperty('sanitize')) {
                $sanitizeProperty = $reflection->getProperty('sanitize');
                $sanitizeProperty->setAccessible(true);
                $sanitizeRules = $sanitizeProperty->getValue($model);

                // Check if the rules are not empty
                if (!empty($sanitizeRules)) {
                    $hasSanitizationRules = true;
                }
            }

            // If no sanitization rules are found, return an error
            if (!$hasSanitizationRules) {
                $this->error("Model {$modelClass} does not have any sanitization rules defined.");
                return Command::FAILURE;
            }
        } catch (\Exception $e) {
            $this->error("Error during command initialization: " . $e->getMessage());
            return Command::FAILURE;
        }

        // Display backup warning
        $this->warn('WARNING: This operation will modify existing records in your database.');
        $this->warn('It is strongly recommended to create a backup of your database before proceeding.');

        // Skip confirmation if --force option is provided or in non-interactive mode
        if ($this->option('force') || !$this->input->isInteractive() || $this->confirm('Do you wish to continue?', true)) {
            // Continue with the operation
        } else {
            $this->info('Operation cancelled.');
            return Command::SUCCESS;
        }

        $this->info("Starting resanitization of {$modelClass} records...");
        $this->info("Processing in chunks of {$chunkSize} records.");

        try {
            $totalRecords = $modelClass::count();
            $processedRecords = 0;
            $updatedRecords = 0;

            $bar = $this->output->createProgressBar($totalRecords);
            $bar->start();

            // Process records in chunks to prevent memory overflow
            $modelClass::query()->chunkById($chunkSize, function ($records) use (&$processedRecords, &$updatedRecords, $bar) {
                DB::beginTransaction();

                try {
                    foreach ($records as $record) {
                        try {
                            $updated = $this->resanitizeRecord($record);
                            $processedRecords++;

                            if ($updated) {
                                $updatedRecords++;
                            }
                        } catch (\Exception $e) {
                            $this->error("Error sanitizing record {$record->id}: " . $e->getMessage());
                            // Continue with next record
                        }

                        $bar->advance();
                    }

                    DB::commit();
                } catch (\Exception $e) {
                    DB::rollBack();
                    $this->error("Error processing chunk: " . $e->getMessage());
                    // Continue with next chunk instead of throwing
                }
            });

            $bar->finish();
            $this->newLine();

            $this->info("Resanitization completed.");
            $this->info("Processed {$processedRecords} records.");
            $this->info("Updated {$updatedRecords} records.");

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Error during sanitization process: " . $e->getMessage());
            return Command::FAILURE;
        }
    }

    /**
     * Check if the model uses the HasSanitization trait.
     *
     * @param Model $model
     * @return bool
     */
    private function usesSanitization(Model $model): bool
    {
        $traits = class_uses_recursive($model);
        return isset($traits[HasSanitization::class]) ||
               isset($traits[\IvanBaric\Sanigen\Traits\Sanigen::class]);
    }

    /**
     * Resanitize a single record.
     *
     * @param Model $record
     * @return bool Whether the record was updated
     */
    private function resanitizeRecord(Model $record): bool
    {
        // Debug: Output the record before sanitization
       # $this->info("Before sanitization: " . json_encode($record->getAttributes()));

        // Use the sanitizeAttributes method from HasSanitization trait
        $updated = $record->sanitizeAttributes();

        // Debug: Output the record after sanitization and whether it was updated
        #$this->info("After sanitization: " . json_encode($record->getAttributes()));
        #$this->info("Updated: " . ($updated ? 'true' : 'false'));

        // Save the record if it was updated, using saveQuietly to avoid infinite loops
        if ($updated) {
            $record->saveQuietly();

            // Debug: Output the record after saving
            #$this->info("After saving: " . json_encode($record->getAttributes()));
        }

        return $updated;
    }
}