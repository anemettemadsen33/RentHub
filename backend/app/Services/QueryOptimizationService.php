<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class QueryOptimizationService
{
    /**
     * Optimize query with eager loading
     */
    public function eagerLoad(Builder $query, array $relations): Builder
    {
        return $query->with($relations);
    }
    
    /**
     * Optimize query with selective loading
     */
    public function selectiveLoad(Builder $query, array $columns): Builder
    {
        return $query->select($columns);
    }
    
    /**
     * Optimize query with chunking
     */
    public function chunk(Builder $query, int $size, \Closure $callback): bool
    {
        return $query->chunk($size, $callback);
    }
    
    /**
     * Optimize query with cursor
     */
    public function cursor(Builder $query): \Illuminate\Support\LazyCollection
    {
        return $query->cursor();
    }
    
    /**
     * Batch insert
     */
    public function batchInsert(string $table, array $data, int $batchSize = 1000): int
    {
        $chunks = array_chunk($data, $batchSize);
        $total = 0;
        
        foreach ($chunks as $chunk) {
            DB::table($table)->insert($chunk);
            $total += count($chunk);
        }
        
        return $total;
    }
    
    /**
     * Batch update
     */
    public function batchUpdate(string $table, array $data, string $keyColumn = 'id'): int
    {
        $updated = 0;
        
        DB::transaction(function () use ($table, $data, $keyColumn, &$updated) {
            foreach ($data as $row) {
                $key = $row[$keyColumn];
                unset($row[$keyColumn]);
                
                $updated += DB::table($table)
                    ->where($keyColumn, $key)
                    ->update($row);
            }
        });
        
        return $updated;
    }
    
    /**
     * Optimize N+1 query
     */
    public function preventNPlusOne(Builder $query, array $relations): Builder
    {
        return $query->with($relations);
    }
    
    /**
     * Use index hint
     */
    public function useIndex(Builder $query, string $index): Builder
    {
        $table = $query->getModel()->getTable();
        return $query->from(DB::raw("{$table} USE INDEX ({$index})"));
    }
    
    /**
     * Force index
     */
    public function forceIndex(Builder $query, string $index): Builder
    {
        $table = $query->getModel()->getTable();
        return $query->from(DB::raw("{$table} FORCE INDEX ({$index})"));
    }
    
    /**
     * Analyze query performance
     */
    public function analyzeQuery(string $sql): array
    {
        $explain = DB::select("EXPLAIN {$sql}");
        
        return [
            'rows_examined' => $explain[0]->rows ?? 0,
            'possible_keys' => $explain[0]->possible_keys ?? null,
            'key_used' => $explain[0]->key ?? null,
            'type' => $explain[0]->type ?? null,
            'extra' => $explain[0]->Extra ?? null,
        ];
    }
    
    /**
     * Get slow queries
     */
    public function getSlowQueries(int $threshold = 1000): array
    {
        return DB::select("
            SELECT * FROM mysql.slow_log 
            WHERE query_time > ?
            ORDER BY query_time DESC 
            LIMIT 100
        ", [$threshold / 1000]);
    }
    
    /**
     * Optimize table
     */
    public function optimizeTable(string $table): bool
    {
        try {
            DB::statement("OPTIMIZE TABLE {$table}");
            return true;
        } catch (\Exception $e) {
            \Log::error("Failed to optimize table {$table}: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Analyze table
     */
    public function analyzeTable(string $table): bool
    {
        try {
            DB::statement("ANALYZE TABLE {$table}");
            return true;
        } catch (\Exception $e) {
            \Log::error("Failed to analyze table {$table}: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Check missing indexes
     */
    public function checkMissingIndexes(string $table): array
    {
        $queries = DB::select("
            SELECT * FROM sys.schema_unused_indexes 
            WHERE object_schema = DATABASE() 
            AND object_name = ?
        ", [$table]);
        
        return $queries;
    }
    
    /**
     * Get index usage statistics
     */
    public function getIndexStats(string $table): array
    {
        $stats = DB::select("
            SELECT 
                index_name,
                seq_in_index,
                column_name,
                cardinality
            FROM information_schema.statistics
            WHERE table_schema = DATABASE()
            AND table_name = ?
            ORDER BY index_name, seq_in_index
        ", [$table]);
        
        return $stats;
    }
    
    /**
     * Connection pooling status
     */
    public function getConnectionPoolStatus(): array
    {
        $status = DB::select("SHOW STATUS LIKE 'Threads%'");
        
        $result = [];
        foreach ($status as $row) {
            $result[$row->Variable_name] = $row->Value;
        }
        
        return $result;
    }
    
    /**
     * Enable query log
     */
    public function enableQueryLog(): void
    {
        DB::enableQueryLog();
    }
    
    /**
     * Get query log
     */
    public function getQueryLog(): array
    {
        return DB::getQueryLog();
    }
    
    /**
     * Find duplicate queries
     */
    public function findDuplicateQueries(): array
    {
        $queries = DB::getQueryLog();
        $grouped = [];
        
        foreach ($queries as $query) {
            $sql = $query['query'];
            if (!isset($grouped[$sql])) {
                $grouped[$sql] = [
                    'query' => $sql,
                    'count' => 0,
                    'total_time' => 0,
                ];
            }
            $grouped[$sql]['count']++;
            $grouped[$sql]['total_time'] += $query['time'];
        }
        
        return array_filter($grouped, fn($q) => $q['count'] > 1);
    }
}
