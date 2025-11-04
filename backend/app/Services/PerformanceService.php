<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Query\Builder;

class PerformanceService
{
    /**
     * Optimize database queries - prevent N+1 problems
     */
    public function optimizeQuery(Builder $query, array $relations = []): Builder
    {
        if (!empty($relations)) {
            $query->with($relations);
        }

        return $query;
    }

    /**
     * Implement cursor pagination for large datasets
     */
    public function cursorPaginate($query, int $perPage = 50, ?string $cursor = null)
    {
        if ($cursor) {
            $decoded = base64_decode($cursor);
            $query->where('id', '>', $decoded);
        }

        $items = $query->limit($perPage + 1)->get();
        $hasMore = $items->count() > $perPage;

        if ($hasMore) {
            $items->pop();
        }

        $nextCursor = $hasMore && $items->isNotEmpty()
            ? base64_encode($items->last()->id)
            : null;

        return [
            'data' => $items,
            'next_cursor' => $nextCursor,
            'has_more' => $hasMore,
        ];
    }

    /**
     * Bulk insert optimization
     */
    public function bulkInsert(string $table, array $data, int $chunkSize = 1000): void
    {
        collect($data)->chunk($chunkSize)->each(function ($chunk) use ($table) {
            DB::table($table)->insert($chunk->toArray());
        });
    }

    /**
     * Bulk update optimization
     */
    public function bulkUpdate(string $table, array $data, string $key = 'id'): void
    {
        foreach ($data as $record) {
            DB::table($table)
                ->where($key, $record[$key])
                ->update($record);
        }
    }

    /**
     * Optimize images for web delivery
     */
    public function optimizeImage(string $path, int $quality = 85): bool
    {
        if (!file_exists($path)) {
            return false;
        }

        $imageInfo = getimagesize($path);
        $mimeType = $imageInfo['mime'] ?? '';

        switch ($mimeType) {
            case 'image/jpeg':
                $image = imagecreatefromjpeg($path);
                return imagejpeg($image, $path, $quality);

            case 'image/png':
                $image = imagecreatefrompng($path);
                imagealphablending($image, false);
                imagesavealpha($image, true);
                return imagepng($image, $path, floor($quality / 10));

            case 'image/webp':
                $image = imagecreatefromwebp($path);
                return imagewebp($image, $path, $quality);

            default:
                return false;
        }
    }

    /**
     * Compress API response
     */
    public function compressResponse(array $data): string
    {
        return gzencode(json_encode($data), 9);
    }

    /**
     * Decompress API response
     */
    public function decompressResponse(string $compressed): array
    {
        $json = gzdecode($compressed);
        return json_decode($json, true) ?? [];
    }

    /**
     * Lazy load relationships
     */
    public function lazyLoad($model, array $relations): void
    {
        if (!$model->relationLoaded($relations[0])) {
            $model->load($relations);
        }
    }

    /**
     * Optimize database indexes
     */
    public function analyzeIndexUsage(string $table): array
    {
        $indexes = DB::select("SHOW INDEX FROM {$table}");
        
        return collect($indexes)->map(function ($index) {
            return [
                'key_name' => $index->Key_name,
                'column_name' => $index->Column_name,
                'cardinality' => $index->Cardinality,
                'index_type' => $index->Index_type,
            ];
        })->toArray();
    }

    /**
     * Monitor slow queries
     */
    public function monitorSlowQueries(float $thresholdMs = 1000): array
    {
        $slowQueries = [];

        DB::listen(function ($query) use (&$slowQueries, $thresholdMs) {
            if ($query->time > $thresholdMs) {
                $slowQueries[] = [
                    'sql' => $query->sql,
                    'bindings' => $query->bindings,
                    'time' => $query->time,
                ];
            }
        });

        return $slowQueries;
    }

    /**
     * Optimize query by adding appropriate indexes
     */
    public function suggestIndexes(string $query): array
    {
        $suggestions = [];
        
        // Parse query for WHERE clauses
        preg_match_all('/WHERE\s+(\w+)\s*[=<>]/', $query, $whereMatches);
        if (!empty($whereMatches[1])) {
            $suggestions[] = [
                'type' => 'simple_index',
                'columns' => array_unique($whereMatches[1]),
            ];
        }

        // Parse query for JOIN clauses
        preg_match_all('/JOIN\s+\w+\s+ON\s+(\w+)\.(\w+)\s*=\s*(\w+)\.(\w+)/', $query, $joinMatches);
        if (!empty($joinMatches[2])) {
            $suggestions[] = [
                'type' => 'join_index',
                'columns' => array_unique($joinMatches[2]),
            ];
        }

        // Parse query for ORDER BY clauses
        preg_match_all('/ORDER\s+BY\s+(\w+)/', $query, $orderMatches);
        if (!empty($orderMatches[1])) {
            $suggestions[] = [
                'type' => 'order_index',
                'columns' => array_unique($orderMatches[1]),
            ];
        }

        return $suggestions;
    }

    /**
     * Implement connection pooling optimization
     */
    public function optimizeConnectionPool(): array
    {
        return [
            'min_connections' => 5,
            'max_connections' => 20,
            'idle_timeout' => 300,
            'connection_timeout' => 30,
            'validation_query' => 'SELECT 1',
        ];
    }
}
