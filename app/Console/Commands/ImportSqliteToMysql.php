<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Copies row data from a Laravel SQLite file into the default (MySQL) database.
 * Intended after `php artisan migrate` on MySQL so schemas match this codebase.
 * Preserves MySQL tables that do not exist in SQLite (e.g. legacy phpMyAdmin-only tables).
 */
class ImportSqliteToMysql extends Command
{
    protected $signature = 'db:import-from-sqlite
                            {--path= : Absolute path to .sqlite file (default: database/database.sqlite)}';

    protected $description = 'Truncate overlapping tables on MySQL (except migrations) and copy rows from SQLite';

    public function handle(): int
    {
        if (config('database.default') !== 'mysql') {
            $this->error('Set DB_CONNECTION=mysql in .env before running this command.');

            return self::FAILURE;
        }

        $path = $this->option('path') ?: database_path('database.sqlite');
        if (! is_file($path)) {
            $this->error("SQLite file not found: {$path}");

            return self::FAILURE;
        }

        config([
            'database.connections.import_source' => [
                'driver' => 'sqlite',
                'database' => $path,
                'prefix' => '',
                'foreign_key_constraints' => false,
            ],
        ]);
        DB::purge('import_source');
        DB::reconnect('import_source');

        $mysql = config('database.default');

        $sqliteTables = $this->tableNames('import_source');
        $mysqlTables = $this->tableNames($mysql);

        $sqliteSet = array_flip($sqliteTables);

        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        foreach ($mysqlTables as $table) {
            if ($table === 'migrations') {
                continue;
            }
            if (! isset($sqliteSet[$table])) {
                continue;
            }
            DB::connection($mysql)->table($table)->truncate();
            $this->line("Truncated {$table}");
        }

        foreach ($sqliteTables as $table) {
            if ($table === 'migrations') {
                continue;
            }
            if (! in_array($table, $mysqlTables, true)) {
                $this->warn("Skipping {$table}: no matching table on MySQL");

                continue;
            }
            $this->copyTable($table, 'import_source', $mysql);
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $this->info('SQLite → MySQL data copy finished.');

        return self::SUCCESS;
    }

    /**
     * @return list<string>
     */
    private function tableNames(string $connection): array
    {
        $driver = Schema::connection($connection)->getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            return collect(DB::connection($connection)->select(
                "SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%' ORDER BY name"
            ))->pluck('name')->map(fn ($n) => (string) $n)->values()->all();
        }

        $dbName = DB::connection($connection)->getDatabaseName();
        $key = 'Tables_in_'.$dbName;

        return collect(DB::connection($connection)->select('SHOW TABLES'))
            ->map(fn ($row) => (string) ((array) $row)[$key])
            ->values()
            ->all();
    }

    private function copyTable(string $table, string $from, string $to): void
    {
        $fromCols = Schema::connection($from)->getColumnListing($table);
        $toCols = Schema::connection($to)->getColumnListing($table);
        $common = array_values(array_intersect($fromCols, $toCols));

        if ($common === []) {
            $this->warn("No common columns for {$table}, skipped.");

            return;
        }

        $count = 0;
        $batch = [];

        foreach (DB::connection($from)->table($table)->cursor() as $row) {
            $arr = (array) $row;
            $line = [];
            foreach ($common as $col) {
                $line[$col] = $arr[$col] ?? null;
            }
            $batch[] = $line;
            $count++;
            if (count($batch) >= 250) {
                DB::connection($to)->table($table)->insert($batch);
                $batch = [];
            }
        }

        if ($batch !== []) {
            DB::connection($to)->table($table)->insert($batch);
        }

        $this->info("Imported {$count} rows → {$table}");
    }
}
