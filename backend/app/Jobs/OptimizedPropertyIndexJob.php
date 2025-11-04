<?php

namespace App\Jobs;

use App\Services\SearchService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class OptimizedPropertyIndexJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 300;

    public $tries = 3;

    public $backoff = [60, 120, 300];

    protected $properties;

    public function __construct(array $properties)
    {
        $this->properties = $properties;
        $this->onQueue('high');
    }

    public function handle(SearchService $searchService)
    {
        $searchService->index('properties', $this->properties);
    }

    public function failed(\Throwable $exception)
    {
        \Log::error('Property indexing failed', [
            'error' => $exception->getMessage(),
            'properties_count' => count($this->properties),
        ]);
    }
}
