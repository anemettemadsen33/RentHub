<?php

namespace App\Console\Commands;

use App\Models\AnalyticsEvent;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PruneAnalyticsEvents extends Command
{
    protected $signature = 'analytics:prune {--days=90 : Retention window in days} {--dry-run : Show counts only, no deletion}';

    protected $description = 'Prune analytics_events older than retention window and archive daily aggregates';

    public function handle(): int
    {
        $days = (int) $this->option('days');
        $dry = $this->option('dry-run');
        $cutoff = now()->subDays($days);

        $this->info("Retention window: last {$days} days (cutoff: {$cutoff->toDateTimeString()})");

        // Aggregate (counts per day per type) for data older than cutoff before deleting.
        $this->info('Calculating archive aggregates...');
        $archiveRows = AnalyticsEvent::query()
            ->selectRaw('DATE(event_timestamp) as day, type, COUNT(*) as cnt')
            ->where('event_timestamp', '<', $cutoff)
            ->groupBy('day', 'type')
            ->orderBy('day')
            ->get();

        $totalEventsToDelete = AnalyticsEvent::query()
            ->where('event_timestamp', '<', $cutoff)
            ->count();

        if ($dry) {
            $this->warn('DRY RUN - no deletion performed');
            $this->table(['Day', 'Type', 'Count'], $archiveRows->map(fn ($r) => [$r->day, $r->type, $r->cnt])->toArray());
            $this->info("Events eligible for deletion: {$totalEventsToDelete}");

            return Command::SUCCESS;
        }

        // Ensure archive table exists (simple structure) - create if missing.
        $this->ensureArchiveTable();

        $this->info('Inserting archive aggregates...');
        $insertBatches = [];
        foreach ($archiveRows as $row) {
            $insertBatches[] = [
                'day' => $row->day,
                'type' => $row->type,
                'count' => $row->cnt,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        if (! empty($insertBatches)) {
            // Upsert (ignore duplicates if re-run) - requires unique index we'll add in migration.
            DB::table('analytics_event_archives')->upsert($insertBatches, ['day', 'type'], ['count', 'updated_at']);
        }

        $this->info('Deleting old events...');
        $deleted = 0;
        // Chunk deletion to avoid locking
        AnalyticsEvent::query()->where('event_timestamp', '<', $cutoff)
            ->orderBy('event_timestamp')
            ->chunkById(5000, function ($chunk) use (&$deleted) {
                $ids = $chunk->pluck('id')->all();
                AnalyticsEvent::whereIn('id', $ids)->delete();
                $deleted += count($ids);
            });

        $this->info("Deleted {$deleted} old events (out of {$totalEventsToDelete} eligible)");
        Log::info('Analytics prune completed', ['deleted' => $deleted, 'retention_days' => $days]);

        return Command::SUCCESS;
    }

    private function ensureArchiveTable(): void
    {
        if (! DB::getSchemaBuilder()->hasTable('analytics_event_archives')) {
            DB::getSchemaBuilder()->create('analytics_event_archives', function ($table) {
                $table->bigIncrements('id');
                $table->date('day');
                $table->string('type');
                $table->unsignedBigInteger('count');
                $table->timestamps();
                $table->unique(['day', 'type']);
            });
        }
    }
}
