<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ExportSqliteCommand extends Command
{
    /**
     * Tables to exclude from export by default.
     * These are transient/regenerated tables.
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

    protected $signature = 'db:export-sqlite
        {--output=database/exports/sqlite-export.sql : Output file path}
        {--tables= : Comma-separated list of tables to export (default: all)}
        {--exclude= : Additional comma-separated list of tables to exclude}
        {--include-excluded : Include normally excluded tables (migrations, cache, etc.)}';

    protected $description = 'Export SQLite database to a PostgreSQL-compatible SQL file';

    public function handle(): int
    {
        $sqlitePath = database_path('database.sqlite');

        if (! File::exists($sqlitePath)) {
            $this->error("SQLite database not found at: {$sqlitePath}");

            return Command::FAILURE;
        }

        $outputPath = $this->option('output');
        $outputDir = dirname($outputPath);

        if (! File::isDirectory($outputDir)) {
            File::makeDirectory($outputDir, 0755, true);
        }

        // Get tables to export
        $tables = $this->getTablesToExport();

        if (empty($tables)) {
            $this->error('No tables to export.');

            return Command::FAILURE;
        }

        $this->info('Exporting SQLite database to PostgreSQL-compatible SQL...');
        $this->newLine();

        // Connect to SQLite
        $sqlite = new \PDO("sqlite:{$sqlitePath}");
        $sqlite->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        $sql = $this->generateHeader();
        $totalRows = 0;

        $this->withProgressBar($tables, function ($table) use ($sqlite, &$sql, &$totalRows) {
            $tableData = $this->exportTable($sqlite, $table);
            $sql .= $tableData['sql'];
            $totalRows += $tableData['rows'];
        });

        $this->newLine(2);

        // Add sequence reset statements
        $sql .= $this->generateSequenceResets($sqlite, $tables);

        // Write to file
        File::put($outputPath, $sql);

        $this->info('Export complete!');
        $this->table(
            ['Metric', 'Value'],
            [
                ['Tables exported', count($tables)],
                ['Total rows', number_format($totalRows)],
                ['Output file', $outputPath],
                ['File size', $this->formatBytes(File::size($outputPath))],
            ]
        );

        return Command::SUCCESS;
    }

    protected function getTablesToExport(): array
    {
        $sqlitePath = database_path('database.sqlite');
        $sqlite = new \PDO("sqlite:{$sqlitePath}");

        // Get all tables from SQLite
        $stmt = $sqlite->query("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'");
        $allTables = $stmt->fetchAll(\PDO::FETCH_COLUMN);

        // If specific tables requested, use those
        if ($this->option('tables')) {
            $requestedTables = array_map('trim', explode(',', $this->option('tables')));

            return array_intersect($requestedTables, $allTables);
        }

        // Otherwise, use all tables minus excluded
        $excludedTables = $this->option('include-excluded')
            ? []
            : $this->defaultExcludedTables;

        // Add user-specified exclusions
        if ($this->option('exclude')) {
            $userExclusions = array_map('trim', explode(',', $this->option('exclude')));
            $excludedTables = array_merge($excludedTables, $userExclusions);
        }

        // Filter out excluded tables
        $tables = array_filter($allTables, function ($table) use ($excludedTables) {
            // Check exact match
            if (in_array($table, $excludedTables)) {
                return false;
            }

            // Check prefix match (e.g., pulse_*)
            foreach ($excludedTables as $excluded) {
                if (str_ends_with($excluded, '*') && str_starts_with($table, rtrim($excluded, '*'))) {
                    return false;
                }
            }

            return true;
        });

        return array_values($tables);
    }

    protected function generateHeader(): string
    {
        $date = now()->toDateTimeString();

        return <<<SQL
-- =============================================================================
-- SQLite to PostgreSQL Export
-- Generated: {$date}
-- Source: database/database.sqlite
-- =============================================================================

-- Disable foreign key checks during import
SET session_replication_role = 'replica';


SQL;
    }

    protected function exportTable(\PDO $sqlite, string $table): array
    {
        $sql = "-- -----------------------------------------------------------------------------\n";
        $sql .= "-- Table: {$table}\n";
        $sql .= "-- -----------------------------------------------------------------------------\n\n";

        // Get all rows
        $stmt = $sqlite->query("SELECT * FROM \"{$table}\"");
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        if (empty($rows)) {
            $sql .= "-- (empty table)\n\n";

            return ['sql' => $sql, 'rows' => 0];
        }

        // Get column names
        $columns = array_keys($rows[0]);
        $columnList = implode(', ', array_map(fn ($col) => "\"{$col}\"", $columns));

        // Generate INSERT statements (batch for efficiency)
        $batchSize = 100;
        $batches = array_chunk($rows, $batchSize);

        foreach ($batches as $batch) {
            $values = [];
            foreach ($batch as $row) {
                $rowValues = [];
                foreach ($row as $value) {
                    $rowValues[] = $this->formatValue($value);
                }
                $values[] = '('.implode(', ', $rowValues).')';
            }

            $sql .= "INSERT INTO \"{$table}\" ({$columnList}) VALUES\n";
            $sql .= implode(",\n", $values).";\n\n";
        }

        return ['sql' => $sql, 'rows' => count($rows)];
    }

    protected function formatValue(mixed $value): string
    {
        if ($value === null) {
            return 'NULL';
        }

        if (is_bool($value)) {
            return $value ? 'TRUE' : 'FALSE';
        }

        if (is_int($value) || is_float($value)) {
            return (string) $value;
        }

        // Handle SQLite boolean integers (0/1 for boolean columns)
        // We can't reliably detect this, so leave as integers

        // Escape string for PostgreSQL
        $escaped = str_replace("'", "''", $value);

        // Handle newlines and special characters
        $escaped = str_replace(["\r\n", "\r", "\n"], '\n', $escaped);

        return "'{$escaped}'";
    }

    protected function generateSequenceResets(\PDO $sqlite, array $tables): string
    {
        $sql = "-- -----------------------------------------------------------------------------\n";
        $sql .= "-- Reset sequences (auto-increment)\n";
        $sql .= "-- -----------------------------------------------------------------------------\n\n";

        foreach ($tables as $table) {
            // Check if table has an 'id' column (most Laravel tables do)
            $stmt = $sqlite->query("PRAGMA table_info(\"{$table}\")");
            $columns = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $hasIdColumn = false;
            foreach ($columns as $column) {
                if ($column['name'] === 'id' && $column['pk'] == 1) {
                    $hasIdColumn = true;
                    break;
                }
            }

            if ($hasIdColumn) {
                // Get max ID
                $stmt = $sqlite->query("SELECT MAX(id) as max_id FROM \"{$table}\"");
                $result = $stmt->fetch(\PDO::FETCH_ASSOC);
                $maxId = $result['max_id'] ?? 0;

                if ($maxId > 0) {
                    $sql .= "SELECT setval('{$table}_id_seq', {$maxId}, true);\n";
                }
            }
        }

        $sql .= "\n-- Re-enable foreign key checks\n";
        $sql .= "SET session_replication_role = 'origin';\n";

        return $sql;
    }

    protected function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $unitIndex = 0;

        while ($bytes >= 1024 && $unitIndex < count($units) - 1) {
            $bytes /= 1024;
            $unitIndex++;
        }

        return round($bytes, 2).' '.$units[$unitIndex];
    }
}
