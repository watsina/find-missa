<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

class ImportToPostgresCommand extends Command
{
    /**
     * Tables to exclude from import by default.
     */
    protected array $defaultExcludedTables = [
        'migrations',
        'cache',
        'cache_locks',
        'sessions',
        'jobs',
        'failed_jobs',
        'pulse_aggregates',
        'pulse_entries',
        'pulse_values',
    ];

    protected $signature = 'db:import-to-postgres
        {--sqlite=database/database.sqlite : Path to SQLite database file}
        {--connection=pgsql : PostgreSQL connection name}
        {--tables= : Comma-separated list of tables to import (default: all)}
        {--exclude= : Additional comma-separated list of tables to exclude}
        {--truncate : Truncate existing data before importing}
        {--skip-missing : Skip tables that do not exist in PostgreSQL (default: true)}
        {--dry-run : Preview what would be imported without executing}';

    protected $description = 'Import data from SQLite database directly into PostgreSQL';

    protected array $stats = [
        'imported' => [],
        'skipped' => [],
        'errors' => [],
    ];

    public function handle(): int
    {
        $sqlitePath = $this->option('sqlite');
        $connection = $this->option('connection');
        $dryRun = $this->option('dry-run');
        $truncate = $this->option('truncate');

        // Validate SQLite file exists
        if (! File::exists($sqlitePath)) {
            $this->error("SQLite database not found at: {$sqlitePath}");

            return Command::FAILURE;
        }

        // Validate PostgreSQL connection
        try {
            DB::connection($connection)->getPdo();
        } catch (\Exception $e) {
            $this->error("Cannot connect to PostgreSQL ({$connection}): ".$e->getMessage());

            return Command::FAILURE;
        }

        // Check if connection is actually PostgreSQL
        $driver = config("database.connections.{$connection}.driver");
        if ($driver !== 'pgsql') {
            $this->error("Connection '{$connection}' is not PostgreSQL (driver: {$driver})");

            return Command::FAILURE;
        }

        $this->info($dryRun ? 'DRY RUN - Previewing import...' : 'Starting import from SQLite to PostgreSQL...');
        $this->newLine();

        // Connect to SQLite
        $sqlite = new \PDO("sqlite:{$sqlitePath}");
        $sqlite->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        // Get tables to import
        $tables = $this->getTablesToImport($sqlite);

        if (empty($tables)) {
            $this->error('No tables to import.');

            return Command::FAILURE;
        }

        $this->info('Tables to import: '.count($tables));

        // Confirm if truncating
        if ($truncate && ! $dryRun && ! $this->confirm('This will DELETE existing data in the target tables. Continue?')) {
            $this->info('Aborted.');

            return Command::FAILURE;
        }

        // Start transaction and defer constraints to avoid foreign key issues during import
        if (! $dryRun) {
            DB::connection($connection)->beginTransaction();
            DB::connection($connection)->statement('SET CONSTRAINTS ALL DEFERRED');
        }

        try {
            $this->withProgressBar($tables, function ($table) use ($sqlite, $connection, $dryRun, $truncate) {
                $this->importTable($sqlite, $table, $connection, $dryRun, $truncate);
            });

            $this->newLine(2);

            // Reset sequences
            if (! $dryRun) {
                $this->info('Resetting sequences...');
                $this->resetSequences($sqlite, $tables, $connection);
            }

            // Commit transaction
            if (! $dryRun) {
                DB::connection($connection)->commit();
            }
        } catch (\Exception $e) {
            // Rollback on error
            if (! $dryRun) {
                DB::connection($connection)->rollBack();
            }
            throw $e;
        }

        // Display results
        $this->displayResults($dryRun);

        return empty($this->stats['errors']) ? Command::SUCCESS : Command::FAILURE;
    }

    protected function getTablesToImport(\PDO $sqlite): array
    {
        // Get all tables from SQLite
        $stmt = $sqlite->query("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'");
        $allTables = $stmt->fetchAll(\PDO::FETCH_COLUMN);

        // If specific tables requested, use those
        if ($this->option('tables')) {
            $requestedTables = array_map('trim', explode(',', $this->option('tables')));

            return array_intersect($requestedTables, $allTables);
        }

        // Build exclusion list
        $excludedTables = $this->defaultExcludedTables;

        if ($this->option('exclude')) {
            $userExclusions = array_map('trim', explode(',', $this->option('exclude')));
            $excludedTables = array_merge($excludedTables, $userExclusions);
        }

        // Filter out excluded tables
        $tables = array_filter($allTables, function ($table) use ($excludedTables) {
            return ! in_array($table, $excludedTables);
        });

        return array_values($tables);
    }

    protected function importTable(\PDO $sqlite, string $table, string $connection, bool $dryRun, bool $truncate): void
    {
        // Check if table exists in PostgreSQL
        if (! Schema::connection($connection)->hasTable($table)) {
            $this->stats['skipped'][$table] = 'Table does not exist in PostgreSQL';

            return;
        }

        // Get row count from SQLite
        $stmt = $sqlite->query("SELECT COUNT(*) FROM \"{$table}\"");
        $rowCount = $stmt->fetchColumn();

        if ($rowCount == 0) {
            $this->stats['skipped'][$table] = 'Empty table';

            return;
        }

        if ($dryRun) {
            $this->stats['imported'][$table] = $rowCount;

            return;
        }

        try {
            // Truncate if requested
            if ($truncate) {
                DB::connection($connection)->table($table)->truncate();
            }

            // Get all rows from SQLite
            $stmt = $sqlite->query("SELECT * FROM \"{$table}\"");
            $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Get PostgreSQL column types to handle boolean conversion
            $pgColumns = $this->getPostgresColumnTypes($table, $connection);

            // Transform and insert in batches
            $batchSize = 500;
            $batches = array_chunk($rows, $batchSize);

            foreach ($batches as $batch) {
                $transformedBatch = array_map(function ($row) use ($pgColumns) {
                    return $this->transformRow($row, $pgColumns);
                }, $batch);

                DB::connection($connection)->table($table)->insert($transformedBatch);
            }

            $this->stats['imported'][$table] = count($rows);

        } catch (\Exception $e) {
            $this->stats['errors'][$table] = $e->getMessage();
        }
    }

    protected function getPostgresColumnTypes(string $table, string $connection): array
    {
        $columns = DB::connection($connection)
            ->select('
                SELECT column_name, data_type 
                FROM information_schema.columns 
                WHERE table_name = ?
            ', [$table]);

        $types = [];
        foreach ($columns as $column) {
            $types[$column->column_name] = $column->data_type;
        }

        return $types;
    }

    protected function transformRow(array $row, array $pgColumns): array
    {
        foreach ($row as $column => $value) {
            if ($value === null) {
                continue;
            }

            $pgType = $pgColumns[$column] ?? 'text';

            // Convert SQLite integer 0/1 to PostgreSQL boolean
            if ($pgType === 'boolean') {
                $row[$column] = (bool) $value;
            }

            // Handle JSON columns
            if (in_array($pgType, ['json', 'jsonb']) && is_string($value)) {
                // Validate JSON
                json_decode($value);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    $row[$column] = null;
                }
            }
        }

        return $row;
    }

    protected function resetSequences(\PDO $sqlite, array $tables, string $connection): void
    {
        foreach ($tables as $table) {
            // Skip if table wasn't imported or doesn't exist
            if (! isset($this->stats['imported'][$table])) {
                continue;
            }

            // Check if table has an id column with a sequence
            try {
                $maxId = DB::connection($connection)->table($table)->max('id');

                if ($maxId !== null) {
                    $sequenceName = "{$table}_id_seq";

                    // Check if sequence exists
                    $sequenceExists = DB::connection($connection)
                        ->select("SELECT 1 FROM pg_sequences WHERE schemaname = 'public' AND sequencename = ?", [$sequenceName]);

                    if (! empty($sequenceExists)) {
                        DB::connection($connection)
                            ->statement("SELECT setval('{$sequenceName}', ?, true)", [$maxId]);
                    }
                }
            } catch (\Exception $e) {
                // Table might not have an id column, skip silently
            }
        }
    }

    protected function displayResults(bool $dryRun): void
    {
        $this->newLine();

        if ($dryRun) {
            $this->info('=== DRY RUN RESULTS ===');
            $this->warn('No data was actually imported.');
            $this->newLine();
        }

        // Imported tables
        if (! empty($this->stats['imported'])) {
            $this->info('Tables '.($dryRun ? 'to be imported' : 'imported').':');
            $rows = [];
            $totalRows = 0;
            foreach ($this->stats['imported'] as $table => $count) {
                $rows[] = [$table, number_format($count)];
                $totalRows += $count;
            }
            $rows[] = ['<fg=green>TOTAL</>', '<fg=green>'.number_format($totalRows).'</>'];
            $this->table(['Table', 'Rows'], $rows);
        }

        // Skipped tables
        if (! empty($this->stats['skipped'])) {
            $this->newLine();
            $this->warn('Tables skipped:');
            $rows = [];
            foreach ($this->stats['skipped'] as $table => $reason) {
                $rows[] = [$table, $reason];
            }
            $this->table(['Table', 'Reason'], $rows);
        }

        // Errors
        if (! empty($this->stats['errors'])) {
            $this->newLine();
            $this->error('Errors encountered:');
            $rows = [];
            foreach ($this->stats['errors'] as $table => $error) {
                $rows[] = [$table, substr($error, 0, 80)];
            }
            $this->table(['Table', 'Error'], $rows);
        }

        // Summary
        $this->newLine();
        $this->info('Summary:');
        $this->line('  Tables imported: '.count($this->stats['imported']));
        $this->line('  Tables skipped: '.count($this->stats['skipped']));
        $this->line('  Errors: '.count($this->stats['errors']));
    }
}
