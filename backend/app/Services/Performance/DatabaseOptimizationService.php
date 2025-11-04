<?php

namespace App\Services\Performance;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DatabaseOptimizationService
{
    /**
     * Analyze and optimize database queries
     */
    public function analyzeQueries(): array
    {
        $slowQueries = DB::select('
            SELECT query_time, lock_time, rows_examined, rows_sent, sql_text
            FROM mysql.slow_log
            ORDER BY query_time DESC
            LIMIT 50
        ');

        return [
            'slow_queries' => $slowQueries,
            'recommendations' => $this->generateRecommendations($slowQueries),
        ];
    }

    /**
     * Optimize table indexes
     */
    public function optimizeIndexes(string $table): array
    {
        $results = [];

        // Analyze table
        $analysis = DB::select("ANALYZE TABLE {$table}");
        $results['analysis'] = $analysis;

        // Check for missing indexes
        $missingIndexes = $this->detectMissingIndexes($table);
        $results['missing_indexes'] = $missingIndexes;

        // Check for unused indexes
        $unusedIndexes = $this->detectUnusedIndexes($table);
        $results['unused_indexes'] = $unusedIndexes;

        // Optimize table
        DB::statement("OPTIMIZE TABLE {$table}");
        $results['optimized'] = true;

        return $results;
    }

    /**
     * Setup connection pooling configuration
     */
    public function configureConnectionPooling(): array
    {
        return [
            'driver' => 'mysql',
            'pool' => [
                'min' => env('DB_POOL_MIN', 2),
                'max' => env('DB_POOL_MAX', 10),
                'idle_timeout' => env('DB_POOL_IDLE_TIMEOUT', 60),
                'wait_timeout' => env('DB_POOL_WAIT_TIMEOUT', 10),
            ],
            'options' => [
                \PDO::ATTR_PERSISTENT => true,
                \PDO::ATTR_TIMEOUT => 5,
                \PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
            ],
        ];
    }

    /**
     * Implement read replica configuration
     */
    public function configureReadReplicas(): array
    {
        return [
            'read' => [
                'host' => [
                    env('DB_READ_HOST_1', '127.0.0.1'),
                    env('DB_READ_HOST_2', '127.0.0.1'),
                ],
            ],
            'write' => [
                'host' => env('DB_WRITE_HOST', '127.0.0.1'),
            ],
            'sticky' => true, // Sticky sessions for write-then-read
        ];
    }

    /**
     * Setup query caching
     */
    public function setupQueryCache(): void
    {
        DB::listen(function ($query) {
            $cacheKey = 'query:'.md5($query->sql.serialize($query->bindings));

            if ($this->isCacheable($query->sql)) {
                Cache::remember($cacheKey, 300, function () use ($query) {
                    return DB::select($query->sql, $query->bindings);
                });
            }
        });
    }

    /**
     * Eliminate N+1 queries
     */
    public function detectNPlusOneQueries(): array
    {
        $queries = [];
        $patterns = [];

        DB::listen(function ($query) use (&$queries, &$patterns) {
            $pattern = preg_replace('/\d+/', '?', $query->sql);

            if (! isset($patterns[$pattern])) {
                $patterns[$pattern] = 0;
            }
            $patterns[$pattern]++;

            if ($patterns[$pattern] > 5) {
                $queries[] = [
                    'pattern' => $pattern,
                    'count' => $patterns[$pattern],
                    'potential_n_plus_one' => true,
                    'suggestion' => 'Consider using eager loading',
                ];
            }
        });

        return $queries;
    }

    /**
     * Generate performance recommendations
     */
    private function generateRecommendations(array $slowQueries): array
    {
        $recommendations = [];

        foreach ($slowQueries as $query) {
            if ($query->rows_examined > 10000) {
                $recommendations[] = [
                    'type' => 'high_row_scan',
                    'query' => substr($query->sql_text, 0, 100),
                    'suggestion' => 'Add index or optimize WHERE clause',
                ];
            }

            if ($query->lock_time > 1) {
                $recommendations[] = [
                    'type' => 'high_lock_time',
                    'query' => substr($query->sql_text, 0, 100),
                    'suggestion' => 'Consider table partitioning or query optimization',
                ];
            }
        }

        return $recommendations;
    }

    /**
     * Detect missing indexes
     */
    private function detectMissingIndexes(string $table): array
    {
        $missing = [];

        // Check for foreign keys without indexes
        $foreignKeys = DB::select('
            SELECT COLUMN_NAME
            FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
            WHERE TABLE_NAME = ? AND REFERENCED_TABLE_NAME IS NOT NULL
        ', [$table]);

        foreach ($foreignKeys as $fk) {
            $hasIndex = DB::select("
                SHOW INDEX FROM {$table}
                WHERE Column_name = ?
            ", [$fk->COLUMN_NAME]);

            if (empty($hasIndex)) {
                $missing[] = [
                    'column' => $fk->COLUMN_NAME,
                    'type' => 'foreign_key',
                    'suggestion' => "CREATE INDEX idx_{$fk->COLUMN_NAME} ON {$table}({$fk->COLUMN_NAME})",
                ];
            }
        }

        return $missing;
    }

    /**
     * Detect unused indexes
     */
    private function detectUnusedIndexes(string $table): array
    {
        return DB::select("
            SELECT DISTINCT
                s.INDEX_NAME,
                s.TABLE_NAME
            FROM INFORMATION_SCHEMA.STATISTICS s
            LEFT JOIN performance_schema.table_io_waits_summary_by_index_usage t
                ON s.TABLE_SCHEMA = t.OBJECT_SCHEMA
                AND s.TABLE_NAME = t.OBJECT_NAME
                AND s.INDEX_NAME = t.INDEX_NAME
            WHERE s.TABLE_NAME = ?
                AND t.INDEX_NAME IS NULL
                AND s.INDEX_NAME != 'PRIMARY'
        ", [$table]);
    }

    /**
     * Check if query is cacheable
     */
    private function isCacheable(string $sql): bool
    {
        $sql = strtoupper($sql);

        // Don't cache write operations
        if (strpos($sql, 'INSERT') !== false ||
            strpos($sql, 'UPDATE') !== false ||
            strpos($sql, 'DELETE') !== false) {
            return false;
        }

        // Don't cache queries with time-sensitive functions
        $timeFunctions = ['NOW()', 'CURRENT_TIMESTAMP', 'RAND()'];
        foreach ($timeFunctions as $func) {
            if (strpos($sql, $func) !== false) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get database performance metrics
     */
    public function getPerformanceMetrics(): array
    {
        return [
            'connections' => $this->getConnectionStats(),
            'query_cache' => $this->getQueryCacheStats(),
            'table_stats' => $this->getTableStats(),
            'slow_queries' => $this->getSlowQueryCount(),
        ];
    }

    private function getConnectionStats(): array
    {
        $stats = DB::select("SHOW STATUS LIKE 'Threads_%'");
        $result = [];
        foreach ($stats as $stat) {
            $result[$stat->Variable_name] = $stat->Value;
        }

        return $result;
    }

    private function getQueryCacheStats(): array
    {
        $stats = DB::select("SHOW STATUS LIKE 'Qcache%'");
        $result = [];
        foreach ($stats as $stat) {
            $result[$stat->Variable_name] = $stat->Value;
        }

        return $result;
    }

    private function getTableStats(): array
    {
        return DB::select('
            SELECT 
                TABLE_NAME,
                TABLE_ROWS,
                DATA_LENGTH,
                INDEX_LENGTH,
                DATA_FREE
            FROM INFORMATION_SCHEMA.TABLES
            WHERE TABLE_SCHEMA = DATABASE()
            ORDER BY DATA_LENGTH DESC
            LIMIT 20
        ');
    }

    private function getSlowQueryCount(): int
    {
        $result = DB::selectOne('
            SELECT COUNT(*) as count
            FROM mysql.slow_log
            WHERE start_time > DATE_SUB(NOW(), INTERVAL 1 HOUR)
        ');

        return $result->count ?? 0;
    }
}
