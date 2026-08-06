<?php

namespace IvanBaric\Sanigen\Console\Commands;

use Illuminate\Console\Command as LaravelCommand;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use IvanBaric\Sanigen\Resolvers\ModelRuleResolver;
use IvanBaric\Sanigen\Traits\HasSanitization;
use IvanBaric\Sanigen\Traits\Sanigen;
use Throwable;

class ResanitizeCommand extends LaravelCommand
{
    private const EXIT_SUCCESS = 0;

    private const EXIT_FAILURE = 1;

    /**
     * The name and signature of the command.
     *
     * @var string
     */
    protected $signature = 'sanigen:resanitize
                            {model : The model class name (e.g., "App\\Models\\Post")}
                            {--chunk=200 : The number of records to process at once}
                            {--tenant= : Restrict a tenant-owned model to one tenant id}
                            {--all-tenants : Explicitly process every tenant}
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

                return self::EXIT_FAILURE;
            }

            // Validate the model class
            if (! class_exists($modelClass)) {
                $this->error("Model class {$modelClass} does not exist.");

                return self::EXIT_FAILURE;
            }

            if (! is_subclass_of($modelClass, Model::class)) {
                $this->error("Model class {$modelClass} must extend ".Model::class.'.');

                return self::EXIT_FAILURE;
            }

            // Create an instance to check if it uses the HasSanitization trait
            $model = new $modelClass;

            if (! $this->usesSanitization($model)) {
                $this->error("Model {$modelClass} does not use the HasSanitization trait.");

                return self::EXIT_FAILURE;
            }

            if (ModelRuleResolver::sanitizeRules($model) === []) {
                $this->error("Model {$modelClass} does not have any sanitization rules defined.");

                return self::EXIT_FAILURE;
            }

            $tenantColumn = (string) config('sanigen.tenant_column', 'team_id');
            $tenantScoped = $tenantColumn !== ''
                && $model->getConnection()->getSchemaBuilder()->hasColumn($model->getTable(), $tenantColumn);
            $tenant = $this->option('tenant');
            $allTenants = (bool) $this->option('all-tenants');

            if ($tenantScoped && (bool) config('sanigen.resanitize_require_tenant_scope', true)) {
                if ($allTenants && is_string($tenant) && trim($tenant) !== '') {
                    $this->error('Use either --tenant or --all-tenants, not both.');

                    return self::EXIT_FAILURE;
                }

                if (! $allTenants && (! is_string($tenant) || ! $this->validTenantIdentifier($tenant))) {
                    $this->error('Tenant-owned models require a valid --tenant value or explicit --all-tenants.');

                    return self::EXIT_FAILURE;
                }

                if ($allTenants && ! (bool) $this->option('force')) {
                    $this->error('--all-tenants requires --force.');

                    return self::EXIT_FAILURE;
                }
            }
        } catch (Throwable $e) {
            report($e);
            $this->error('Error during command initialization: '.$e->getMessage());

            return self::EXIT_FAILURE;
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

            return self::EXIT_SUCCESS;
        }

        $this->info(($dryRun ? 'Checking' : 'Starting resanitization of')." {$modelClass} records...");
        $this->info("Processing in chunks of {$chunkSize} records.");

        try {
            $query = $model->newQuery();

            if ($tenantScoped) {
                $tenantScope = 'IvanBaric\\Corexis\\Database\\Scopes\\TenantScope';

                if (class_exists($tenantScope)) {
                    $query->withoutGlobalScope($tenantScope);
                }

                if (! $allTenants) {
                    $query->where($tenantColumn, trim((string) $tenant));
                }
            }

            $totalRecords = $query->count();
            $processedRecords = 0;
            $updatedRecords = 0;
            $failedRecords = 0;
            $failedChunks = 0;

            $bar = $this->output->createProgressBar($totalRecords);
            $bar->start();

            // Process records in chunks to prevent memory overflow
            $query->chunkById($chunkSize, function ($records) use (&$processedRecords, &$updatedRecords, &$failedRecords, &$failedChunks, $bar, $dryRun) {
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
                            report($e);
                            $failedRecords++;
                            $this->error("Error sanitizing record key {$record->getKey()}: ".$e->getMessage());
                            // Continue with next record
                        }

                        $bar->advance();
                    }

                    if (! $dryRun) {
                        DB::commit();
                    }
                } catch (Throwable $e) {
                    report($e);
                    $failedChunks++;

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

            if ($failedRecords > 0 || $failedChunks > 0) {
                $this->error("Resanitization completed with {$failedRecords} failed record(s) and {$failedChunks} failed chunk(s).");

                return self::EXIT_FAILURE;
            }

            return self::EXIT_SUCCESS;
        } catch (Throwable $e) {
            report($e);
            $this->error('Error during sanitization process: '.$e->getMessage());

            return self::EXIT_FAILURE;
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

    private function validTenantIdentifier(string $tenant): bool
    {
        $tenant = trim($tenant);

        return $tenant !== ''
            && strlen($tenant) <= 64
            && preg_match('/^[A-Za-z0-9_-]+$/', $tenant) === 1;
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
