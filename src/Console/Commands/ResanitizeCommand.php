<?php

namespace IvanBaric\Sanigen\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use IvanBaric\Sanigen\Resolvers\ModelRuleResolver;
use IvanBaric\Sanigen\Traits\HasSanitization;
use IvanBaric\Sanigen\Traits\Sanigen;
use Throwable;

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
                            {--dry-run : Count records that would change without saving}
                            {--force : Skip confirmation prompt}';

    /**
     * The command description.
     *
     * @var string
     */
    protected $description = 'Re-apply sanitization rules to existing model records';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        try {
            $modelClass = $this->argument('model');
            $chunkSize = (int) $this->option('chunk');
            $maxChunkSize = max(1, (int) config('sanigen.resanitize_max_chunk', 1000));

            if ($chunkSize < 1 || $chunkSize > $maxChunkSize) {
                $this->error("Chunk size must be between 1 and {$maxChunkSize}.");

                return Command::FAILURE;
            }

            // Validate the model class
            if (! class_exists($modelClass)) {
                $this->error("Model class {$modelClass} does not exist.");

                return Command::FAILURE;
            }

            if (! is_subclass_of($modelClass, Model::class)) {
                $this->error("Model class {$modelClass} must extend ".Model::class.'.');

                return Command::FAILURE;
            }

            // Create an instance to check if it uses the HasSanitization trait
            $model = new $modelClass;

            if (! $this->usesSanitization($model)) {
                $this->error("Model {$modelClass} does not use the HasSanitization trait.");

                return Command::FAILURE;
            }

            if (ModelRuleResolver::sanitizeRules($model) === []) {
                $this->error("Model {$modelClass} does not have any sanitization rules defined.");

                return Command::FAILURE;
            }
        } catch (Throwable $e) {
            $this->error('Error during command initialization: '.$e->getMessage());

            return Command::FAILURE;
        }

        $dryRun = (bool) $this->option('dry-run');

        // Display backup warning
        if ($dryRun) {
            $this->warn('DRY RUN: No records will be saved.');
        } else {
            $this->warn('WARNING: This operation will modify existing records in your database.');
            $this->warn('It is strongly recommended to create a backup of your database before proceeding.');
        }

        // Skip confirmation if --force option is provided or in non-interactive mode
        if ($this->option('force') || ! $this->input->isInteractive() || $this->confirm('Do you wish to continue?', true)) {
            // Continue with the operation
        } else {
            $this->info('Operation cancelled.');

            return Command::SUCCESS;
        }

        $this->info(($dryRun ? 'Checking' : 'Starting resanitization of')." {$modelClass} records...");
        $this->info("Processing in chunks of {$chunkSize} records.");

        try {
            $query = $model->newQuery();
            $totalRecords = $query->count();
            $processedRecords = 0;
            $updatedRecords = 0;

            $bar = $this->output->createProgressBar($totalRecords);
            $bar->start();

            // Process records in chunks to prevent memory overflow
            $query->chunkById($chunkSize, function ($records) use (&$processedRecords, &$updatedRecords, $bar, $dryRun) {
                if (! $dryRun) {
                    DB::beginTransaction();
                }

                try {
                    foreach ($records as $record) {
                        try {
                            $updated = $this->resanitizeRecord($record, $dryRun);
                            $processedRecords++;

                            if ($updated) {
                                $updatedRecords++;
                            }
                        } catch (Throwable $e) {
                            $this->error("Error sanitizing record key {$record->getKey()}: ".$e->getMessage());
                            // Continue with next record
                        }

                        $bar->advance();
                    }

                    if (! $dryRun) {
                        DB::commit();
                    }
                } catch (Throwable $e) {
                    if (! $dryRun) {
                        DB::rollBack();
                    }

                    $this->error('Error processing chunk: '.$e->getMessage());
                    // Continue with next chunk instead of throwing
                }
            });

            $bar->finish();
            $this->newLine();

            $this->info($dryRun ? 'Dry run completed.' : 'Resanitization completed.');
            $this->info("Processed {$processedRecords} records.");
            $this->info($dryRun ? "Would update {$updatedRecords} records." : "Updated {$updatedRecords} records.");

            return Command::SUCCESS;
        } catch (Throwable $e) {
            $this->error('Error during sanitization process: '.$e->getMessage());

            return Command::FAILURE;
        }
    }

    /**
     * Check if the model uses the HasSanitization trait.
     */
    private function usesSanitization(Model $model): bool
    {
        $traits = class_uses_recursive($model);

        return isset($traits[HasSanitization::class]) ||
               isset($traits[Sanigen::class]);
    }

    /**
     * Resanitize a single record.
     *
     * @return bool Whether the record was updated
     */
    private function resanitizeRecord(Model $record, bool $dryRun = false): bool
    {
        if (! method_exists($record, 'sanitizeAttributes')) {
            return false;
        }

        $updated = $record->sanitizeAttributes();

        if ($updated && ! $dryRun) {
            $record->saveQuietly();
        }

        return $updated;
    }
}
