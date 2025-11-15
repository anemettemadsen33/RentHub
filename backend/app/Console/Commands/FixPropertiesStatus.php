<?php

namespace App\Console\Commands;

use App\Models\Property;
use Illuminate\Console\Command;

class FixPropertiesStatus extends Command
{
    protected $signature = 'properties:fix-status {--all : Force set status for all properties}';

    protected $description = 'Ensure properties have status=available and is_active=1 (idempotent)';

    public function handle(): int
    {
        $query = Property::query();
        if (! $this->option('all')) {
            $query->whereNull('status')->orWhere('status', '')->orWhere('status', 'draft');
        }

        $count = 0;
        $query->chunkById(200, function ($chunk) use (&$count) {
            foreach ($chunk as $prop) {
                $prop->status = 'available';
                $prop->is_active = true;
                $prop->save();
                $count++;
            }
        });

        $this->info("Updated {$count} properties to status=available");
        return 0;
    }
}
