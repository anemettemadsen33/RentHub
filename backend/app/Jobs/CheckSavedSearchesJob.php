<?php

namespace App\Jobs;

use App\Models\Property;
use App\Models\SavedSearch;
use App\Models\SavedSearchMatch;
use App\Notifications\NewPropertyMatchNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CheckSavedSearchesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 300; // 5 minutes

    protected ?int $propertyId;
    protected ?int $savedSearchId;

    /**
     * Create a new job instance.
     */
    public function __construct(?int $propertyId = null, ?int $savedSearchId = null)
    {
        $this->propertyId = $propertyId;
        $this->savedSearchId = $savedSearchId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('CheckSavedSearchesJob started', [
            'property_id' => $this->propertyId,
            'saved_search_id' => $this->savedSearchId,
        ]);

        if ($this->savedSearchId) {
            // Check specific saved search
            $this->checkSavedSearch($this->savedSearchId);
        } elseif ($this->propertyId) {
            // Check all active saved searches against new property
            $this->checkPropertyAgainstSavedSearches($this->propertyId);
        } else {
            // Check all active saved searches (scheduled job)
            $this->checkAllSavedSearches();
        }
    }

    /**
     * Check specific saved search for new matches
     */
    protected function checkSavedSearch(int $savedSearchId): void
    {
        $savedSearch = SavedSearch::with('user')->find($savedSearchId);

        if (!$savedSearch || !$savedSearch->is_active) {
            return;
        }

        $matches = $savedSearch->getMatchingProperties();
        $newMatches = [];

        foreach ($matches as $property) {
            // Check if already matched
            $existingMatch = SavedSearchMatch::where('saved_search_id', $savedSearch->id)
                ->where('property_id', $property->id)
                ->first();

            if (!$existingMatch) {
                // Create new match
                $match = SavedSearchMatch::create([
                    'saved_search_id' => $savedSearch->id,
                    'property_id' => $property->id,
                    'notified' => false,
                ]);

                $newMatches[] = $property;
            }
        }

        // Send notification if there are new matches
        if (!empty($newMatches) && $savedSearch->email_notifications) {
            $this->sendNotification($savedSearch, $newMatches);
        }
    }

    /**
     * Check new property against all saved searches
     */
    protected function checkPropertyAgainstSavedSearches(int $propertyId): void
    {
        $property = Property::with('amenities')->find($propertyId);

        if (!$property || $property->status !== 'available') {
            return;
        }

        $savedSearches = SavedSearch::where('is_active', true)
            ->with('user')
            ->get();

        foreach ($savedSearches as $savedSearch) {
            if ($savedSearch->matchesCriteria($property)) {
                // Check if already matched
                $existingMatch = SavedSearchMatch::where('saved_search_id', $savedSearch->id)
                    ->where('property_id', $property->id)
                    ->first();

                if (!$existingMatch) {
                    // Create match
                    SavedSearchMatch::create([
                        'saved_search_id' => $savedSearch->id,
                        'property_id' => $property->id,
                        'notified' => false,
                    ]);

                    // Send instant notification
                    if ($savedSearch->email_notifications && $savedSearch->frequency === 'instant') {
                        $this->sendNotification($savedSearch, [$property]);
                    }
                }
            }
        }
    }

    /**
     * Check all active saved searches (for scheduled job)
     */
    protected function checkAllSavedSearches(): void
    {
        $savedSearches = SavedSearch::where('is_active', true)
            ->with('user')
            ->get();

        foreach ($savedSearches as $savedSearch) {
            // Check frequency
            if (!$this->shouldNotify($savedSearch)) {
                continue;
            }

            // Get unnotified matches
            $unnotifiedMatches = $savedSearch->unnotifiedMatches()
                ->with('property')
                ->get();

            if ($unnotifiedMatches->isNotEmpty()) {
                $properties = $unnotifiedMatches->pluck('property')->filter();

                if ($properties->isNotEmpty() && $savedSearch->email_notifications) {
                    $this->sendNotification($savedSearch, $properties->toArray());

                    // Mark as notified
                    $unnotifiedMatches->each(function ($match) {
                        $match->update([
                            'notified' => true,
                            'notified_at' => now(),
                        ]);
                    });
                }
            }
        }
    }

    /**
     * Check if should notify based on frequency
     */
    protected function shouldNotify(SavedSearch $savedSearch): bool
    {
        if ($savedSearch->frequency === 'instant') {
            return true;
        }

        if (!$savedSearch->last_notified_at) {
            return true;
        }

        $hoursSinceLastNotification = $savedSearch->last_notified_at->diffInHours(now());

        if ($savedSearch->frequency === 'daily' && $hoursSinceLastNotification >= 24) {
            return true;
        }

        if ($savedSearch->frequency === 'weekly' && $hoursSinceLastNotification >= 168) {
            return true;
        }

        return false;
    }

    /**
     * Send notification to user
     */
    protected function sendNotification(SavedSearch $savedSearch, array $properties): void
    {
        try {
            $savedSearch->user->notify(new NewPropertyMatchNotification($savedSearch, $properties));

            $savedSearch->update([
                'last_notified_at' => now(),
                'notification_count' => $savedSearch->notification_count + 1,
            ]);

            Log::info('Sent saved search notification', [
                'saved_search_id' => $savedSearch->id,
                'user_id' => $savedSearch->user_id,
                'property_count' => count($properties),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send saved search notification', [
                'saved_search_id' => $savedSearch->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
