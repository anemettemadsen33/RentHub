<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class SearchService
{
    protected $config;
    protected $driver;

    public function __construct()
    {
        $this->config = config('api.search');
        $this->driver = $this->config['driver'];
    }

    public function search(string $index, string $query, array $filters = [], array $options = []): array
    {
        return match($this->driver) {
            'meilisearch' => $this->searchMeilisearch($index, $query, $filters, $options),
            'elasticsearch' => $this->searchElasticsearch($index, $query, $filters, $options),
            default => throw new \Exception("Unsupported search driver: {$this->driver}")
        };
    }

    protected function searchMeilisearch(string $index, string $query, array $filters, array $options): array
    {
        $config = $this->config['meilisearch'];
        $url = "{$config['host']}/indexes/{$index}/search";

        $params = [
            'q' => $query,
            'limit' => $options['limit'] ?? 20,
            'offset' => $options['offset'] ?? 0,
        ];

        if (!empty($filters)) {
            $params['filter'] = $this->buildMeilisearchFilters($filters);
        }

        if (isset($options['sort'])) {
            $params['sort'] = $options['sort'];
        }

        $response = Http::withHeaders([
            'Authorization' => "Bearer {$config['key']}",
        ])->post($url, $params);

        return $response->json();
    }

    protected function searchElasticsearch(string $index, string $query, array $filters, array $options): array
    {
        $config = $this->config['elasticsearch'];
        $indexName = $config['index_prefix'] . $index;
        $url = "{$config['hosts'][0]}/{$indexName}/_search";

        $body = [
            'query' => [
                'bool' => [
                    'must' => [
                        'multi_match' => [
                            'query' => $query,
                            'fields' => ['*'],
                        ],
                    ],
                ],
            ],
            'from' => $options['offset'] ?? 0,
            'size' => $options['limit'] ?? 20,
        ];

        if (!empty($filters)) {
            $body['query']['bool']['filter'] = $this->buildElasticsearchFilters($filters);
        }

        $response = Http::post($url, $body);
        
        return $response->json();
    }

    public function index(string $index, array $documents): bool
    {
        return match($this->driver) {
            'meilisearch' => $this->indexMeilisearch($index, $documents),
            'elasticsearch' => $this->indexElasticsearch($index, $documents),
            default => false
        };
    }

    protected function indexMeilisearch(string $index, array $documents): bool
    {
        $config = $this->config['meilisearch'];
        $url = "{$config['host']}/indexes/{$index}/documents";

        $response = Http::withHeaders([
            'Authorization' => "Bearer {$config['key']}",
        ])->post($url, $documents);

        return $response->successful();
    }

    protected function indexElasticsearch(string $index, array $documents): bool
    {
        $config = $this->config['elasticsearch'];
        $indexName = $config['index_prefix'] . $index;
        
        $body = [];
        foreach ($documents as $doc) {
            $body[] = json_encode(['index' => ['_index' => $indexName, '_id' => $doc['id']]]);
            $body[] = json_encode($doc);
        }

        $url = "{$config['hosts'][0]}/_bulk";
        $response = Http::withHeaders(['Content-Type' => 'application/x-ndjson'])
            ->withBody(implode("\n", $body) . "\n", 'application/x-ndjson')
            ->post($url);

        return $response->successful();
    }

    public function delete(string $index, $documentId): bool
    {
        return match($this->driver) {
            'meilisearch' => $this->deleteMeilisearch($index, $documentId),
            'elasticsearch' => $this->deleteElasticsearch($index, $documentId),
            default => false
        };
    }

    protected function deleteMeilisearch(string $index, $documentId): bool
    {
        $config = $this->config['meilisearch'];
        $url = "{$config['host']}/indexes/{$index}/documents/{$documentId}";

        $response = Http::withHeaders([
            'Authorization' => "Bearer {$config['key']}",
        ])->delete($url);

        return $response->successful();
    }

    protected function deleteElasticsearch(string $index, $documentId): bool
    {
        $config = $this->config['elasticsearch'];
        $indexName = $config['index_prefix'] . $index;
        $url = "{$config['hosts'][0]}/{$indexName}/_doc/{$documentId}";

        $response = Http::delete($url);
        
        return $response->successful();
    }

    protected function buildMeilisearchFilters(array $filters): string
    {
        $filterStrings = [];
        
        foreach ($filters as $field => $value) {
            if (is_array($value)) {
                $values = array_map(fn($v) => is_string($v) ? "\"{$v}\"" : $v, $value);
                $filterStrings[] = "{$field} IN [" . implode(', ', $values) . "]";
            } else {
                $filterStrings[] = is_string($value) ? "{$field} = \"{$value}\"" : "{$field} = {$value}";
            }
        }
        
        return implode(' AND ', $filterStrings);
    }

    protected function buildElasticsearchFilters(array $filters): array
    {
        $terms = [];
        
        foreach ($filters as $field => $value) {
            if (is_array($value)) {
                $terms[] = ['terms' => [$field => $value]];
            } else {
                $terms[] = ['term' => [$field => $value]];
            }
        }
        
        return $terms;
    }
}
