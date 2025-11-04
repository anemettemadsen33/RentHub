<?php

namespace App\Services;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class APIOptimizationService
{
    /**
     * Apply response compression
     */
    public function compress(string $data, string $algorithm = 'gzip'): string
    {
        return match ($algorithm) {
            'gzip' => gzencode($data, 9),
            'deflate' => gzdeflate($data, 9),
            'br', 'brotli' => function_exists('brotli_compress') ? brotli_compress($data) : gzencode($data, 9),
            default => $data,
        };
    }

    /**
     * Paginate results
     */
    public function paginate($query, Request $request, int $defaultPerPage = 15): LengthAwarePaginator
    {
        $perPage = $request->input('per_page', $defaultPerPage);
        $perPage = min(max($perPage, 1), 100); // Limit between 1 and 100

        return $query->paginate($perPage);
    }

    /**
     * Apply field selection
     */
    public function selectFields($query, Request $request): mixed
    {
        $fields = $request->input('fields');

        if (! $fields) {
            return $query;
        }

        $fieldsArray = is_string($fields) ? explode(',', $fields) : $fields;

        // Ensure ID is always included
        if (! in_array('id', $fieldsArray)) {
            $fieldsArray[] = 'id';
        }

        return $query->select($fieldsArray);
    }

    /**
     * Apply sorting
     */
    public function applySorting($query, Request $request): mixed
    {
        $sortBy = $request->input('sort_by', 'id');
        $sortOrder = $request->input('sort_order', 'desc');

        return $query->orderBy($sortBy, $sortOrder);
    }

    /**
     * Apply filtering
     */
    public function applyFilters($query, Request $request, array $allowedFilters): mixed
    {
        foreach ($allowedFilters as $filter) {
            $value = $request->input($filter);

            if ($value !== null) {
                $query->where($filter, $value);
            }
        }

        return $query;
    }

    /**
     * Format API response
     */
    public function formatResponse($data, ?string $message = null, int $status = 200): JsonResponse
    {
        $response = [
            'success' => $status >= 200 && $status < 300,
            'data' => $data,
        ];

        if ($message) {
            $response['message'] = $message;
        }

        if ($data instanceof LengthAwarePaginator) {
            $response['meta'] = [
                'current_page' => $data->currentPage(),
                'last_page' => $data->lastPage(),
                'per_page' => $data->perPage(),
                'total' => $data->total(),
                'from' => $data->firstItem(),
                'to' => $data->lastItem(),
            ];

            $response['links'] = [
                'first' => $data->url(1),
                'last' => $data->url($data->lastPage()),
                'prev' => $data->previousPageUrl(),
                'next' => $data->nextPageUrl(),
            ];

            $response['data'] = $data->items();
        }

        return response()->json($response, $status);
    }

    /**
     * Format error response
     */
    public function formatError(string $message, $errors = null, int $status = 400): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if ($errors) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $status);
    }

    /**
     * Apply includes (eager loading)
     */
    public function applyIncludes($query, Request $request, array $allowedIncludes): mixed
    {
        $includes = $request->input('include');

        if (! $includes) {
            return $query;
        }

        $includesArray = is_string($includes) ? explode(',', $includes) : $includes;
        $includesArray = array_intersect($includesArray, $allowedIncludes);

        return $query->with($includesArray);
    }

    /**
     * Apply search
     */
    public function applySearch($query, Request $request, array $searchableFields): mixed
    {
        $search = $request->input('search');

        if (! $search) {
            return $query;
        }

        return $query->where(function ($q) use ($search, $searchableFields) {
            foreach ($searchableFields as $field) {
                $q->orWhere($field, 'LIKE', "%{$search}%");
            }
        });
    }

    /**
     * Cache API response
     */
    public function cacheResponse(Request $request, \Closure $callback, int $ttl = 300): JsonResponse
    {
        $cacheKey = $this->generateCacheKey($request);

        $cachedResponse = cache()->remember($cacheKey, $ttl, function () use ($callback) {
            return $callback();
        });

        return $cachedResponse;
    }

    /**
     * Generate cache key from request
     */
    private function generateCacheKey(Request $request): string
    {
        $key = 'api:'.$request->path().':'.md5(json_encode([
            'query' => $request->query(),
            'user' => $request->user()?->id,
        ]));

        return $key;
    }

    /**
     * Add ETag header
     */
    public function addETag(JsonResponse $response): JsonResponse
    {
        $etag = md5($response->getContent());
        $response->setEtag($etag);

        return $response;
    }

    /**
     * Add cache headers
     */
    public function addCacheHeaders(JsonResponse $response, int $maxAge = 300): JsonResponse
    {
        $response->header('Cache-Control', "public, max-age={$maxAge}");
        $response->header('Expires', now()->addSeconds($maxAge)->toRfc7231String());

        return $response;
    }

    /**
     * Enable compression header
     */
    public function enableCompression(JsonResponse $response): JsonResponse
    {
        $response->header('Content-Encoding', 'gzip');

        return $response;
    }

    /**
     * Apply rate limit headers
     */
    public function addRateLimitHeaders(JsonResponse $response, int $limit, int $remaining): JsonResponse
    {
        $response->header('X-RateLimit-Limit', $limit);
        $response->header('X-RateLimit-Remaining', $remaining);
        $response->header('X-RateLimit-Reset', now()->addMinutes(1)->timestamp);

        return $response;
    }

    /**
     * Optimize JSON response
     */
    public function optimizeJSON($data): array
    {
        // Remove null values
        return $this->removeNullValues($data);
    }

    /**
     * Remove null values recursively
     */
    private function removeNullValues($data): array
    {
        if (! is_array($data)) {
            return $data;
        }

        return array_filter(array_map(function ($value) {
            if (is_array($value)) {
                return $this->removeNullValues($value);
            }

            return $value;
        }, $data), function ($value) {
            return $value !== null;
        });
    }

    /**
     * Batch API requests
     */
    public function batchRequests(array $requests): array
    {
        $responses = [];

        foreach ($requests as $key => $request) {
            try {
                $responses[$key] = $this->executeRequest($request);
            } catch (\Exception $e) {
                $responses[$key] = [
                    'success' => false,
                    'error' => $e->getMessage(),
                ];
            }
        }

        return $responses;
    }

    /**
     * Execute single request
     */
    private function executeRequest(array $request): array
    {
        // Implementation depends on your routing structure
        return [];
    }
}
