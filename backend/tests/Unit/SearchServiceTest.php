<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\SearchService;
use Illuminate\Support\Facades\Http;

class SearchServiceTest extends TestCase
{
    protected SearchService $searchService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->searchService = new SearchService();
    }

    public function test_can_search_properties()
    {
        Http::fake([
            '*/indexes/properties/search' => Http::response([
                'hits' => [
                    ['id' => 1, 'title' => 'Test Property'],
                ],
                'estimatedTotalHits' => 1,
            ]),
        ]);

        $results = $this->searchService->search('properties', 'apartment');

        $this->assertIsArray($results);
        $this->assertArrayHasKey('hits', $results);
    }

    public function test_can_index_documents()
    {
        Http::fake([
            '*/indexes/properties/documents' => Http::response(['uid' => 123]),
        ]);

        $result = $this->searchService->index('properties', [
            ['id' => 1, 'title' => 'Test Property'],
        ]);

        $this->assertTrue($result);
    }

    public function test_can_delete_document()
    {
        Http::fake([
            '*/indexes/properties/documents/*' => Http::response(['uid' => 123]),
        ]);

        $result = $this->searchService->delete('properties', 1);

        $this->assertTrue($result);
    }

    public function test_handles_search_filters()
    {
        Http::fake([
            '*/indexes/properties/search' => Http::response([
                'hits' => [],
                'estimatedTotalHits' => 0,
            ]),
        ]);

        $results = $this->searchService->search('properties', 'apartment', [
            'type' => 'apartment',
            'price' => [1000, 2000],
        ]);

        $this->assertIsArray($results);
    }
}
