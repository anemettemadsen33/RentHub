<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DatabaseOptimizationService
{
    public function analyzeQueryPerformance(): array
    {
        $slowQueries = DB::select('
            SELECT query_time, sql_text
            FROM performance_schema.events_statements_history
            WHERE query_time > 1
            ORDER BY query_time DESC
            LIMIT 50
        ');

        return [
            'slow_queries' => $slowQueries,
            'recommendations' => $this->generateRecommendations($slowQueries),
        ];
    }

    public function optimizeTable(string $table): void
    {
        DB::statement("OPTIMIZE TABLE {$table}");
    }

    public function analyzeTable(string $table): void
    {
        DB::statement("ANALYZE TABLE {$table}");
    }

    public function checkMissingIndexes(string $table): array
    {
        $columns = Schema::getColumnListing($table);
        $indexes = $this->getTableIndexes($table);

        $foreignKeyColumns = $this->getForeignKeyColumns($table);
        $missingIndexes = [];

        foreach ($foreignKeyColumns as $column) {
            if (! $this->hasIndex($indexes, $column)) {
                $missingIndexes[] = $column;
            }
        }

        return $missingIndexes;
    }

    public function getTableSize(string $table): array
    {
        $result = DB::selectOne('
            SELECT 
                table_name,
                ROUND(((data_length + index_length) / 1024 / 1024), 2) AS size_mb,
                table_rows
            FROM information_schema.TABLES
            WHERE table_schema = DATABASE()
            AND table_name = ?
        ', [$table]);

        return (array) $result;
    }

    public function getDatabaseStatistics(): array
    {
        $tables = DB::select('
            SELECT 
                table_name,
                ROUND(((data_length + index_length) / 1024 / 1024), 2) AS size_mb,
                table_rows,
                ROUND((index_length / 1024 / 1024), 2) AS index_size_mb
            FROM information_schema.TABLES
            WHERE table_schema = DATABASE()
            ORDER BY (data_length + index_length) DESC
        ');

        return [
            'tables' => $tables,
            'total_size' => array_sum(array_column($tables, 'size_mb')),
            'total_rows' => array_sum(array_column($tables, 'table_rows')),
        ];
    }

    protected function getTableIndexes(string $table): array
    {
        return DB::select("SHOW INDEXES FROM {$table}");
    }

    protected function getForeignKeyColumns(string $table): array
    {
        $foreignKeys = DB::select('
            SELECT COLUMN_NAME
            FROM information_schema.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = DATABASE()
            AND TABLE_NAME = ?
            AND REFERENCED_TABLE_NAME IS NOT NULL
        ', [$table]);

        return array_column($foreignKeys, 'COLUMN_NAME');
    }

    protected function hasIndex(array $indexes, string $column): bool
    {
        foreach ($indexes as $index) {
            if ($index->Column_name === $column) {
                return true;
            }
        }

        return false;
    }

    protected function generateRecommendations(array $slowQueries): array
    {
        $recommendations = [];

        foreach ($slowQueries as $query) {
            if (stripos($query->sql_text, 'SELECT') !== false &&
                stripos($query->sql_text, 'WHERE') !== false &&
                stripos($query->sql_text, 'INDEX') === false) {
                $recommendations[] = 'Consider adding indexes for WHERE clause columns';
            }

            if (stripos($query->sql_text, 'SELECT *') !== false) {
                $recommendations[] = 'Avoid SELECT * - specify only needed columns';
            }

            if (stripos($query->sql_text, 'ORDER BY') !== false) {
                $recommendations[] = 'Consider adding indexes for ORDER BY columns';
            }
        }

        return array_unique($recommendations);
    }
}
