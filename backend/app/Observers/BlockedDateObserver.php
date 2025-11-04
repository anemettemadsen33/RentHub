<?php

namespace App\Observers;

use App\Models\BlockedDate;
use App\Models\GoogleCalendarToken;
use App\Services\GoogleCalendarService;
use Illuminate\Support\Facades\Log;

class BlockedDateObserver
{
    public function __construct(
        private GoogleCalendarService $googleCalendarService
    ) {
    }

    /**
     * Handle the BlockedDate "created" event.
     */
    public function created(BlockedDate $blockedDate): void
    {
        $this->syncToGoogleCalendar($blockedDate);
    }

    /**
     * Handle the BlockedDate "updated" event.
     */
    public function updated(BlockedDate $blockedDate): void
    {
        if ($blockedDate->isDirty(['start_date', 'end_date', 'reason'])) {
            $this->syncToGoogleCalendar($blockedDate);
        }
    }

    /**
     * Handle the BlockedDate "deleted" event.
     */
    public function deleted(BlockedDate $blockedDate): void
    {
        $this->removeFromGoogleCalendar($blockedDate);
    }

    /**
     * Sync blocked date to Google Calendar
     */
    private function syncToGoogleCalendar(BlockedDate $blockedDate): void
    {
        try {
            // Get Google Calendar tokens for this property
            $tokens = GoogleCalendarToken::where('property_id', $blockedDate->property_id)
                ->where('sync_enabled', true)
                ->get();

            foreach ($tokens as $token) {
                $this->googleCalendarService->syncBlockedDateToGoogle($blockedDate, $token);
            }
        } catch (\Exception $e) {
            Log::error('Failed to sync blocked date to Google Calendar', [
                'blocked_date_id' => $blockedDate->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Remove blocked date from Google Calendar
     */
    private function removeFromGoogleCalendar(BlockedDate $blockedDate): void
    {
        if (!$blockedDate->google_event_id) {
            return;
        }

        try {
            // Get Google Calendar tokens for this property
            $tokens = GoogleCalendarToken::where('property_id', $blockedDate->property_id)
                ->where('sync_enabled', true)
                ->get();

            foreach ($tokens as $token) {
                $service = new \Google\Client();
                $service->setAccessToken($token->access_token);
                $calendarService = new \Google\Service\Calendar($service);
                $calendarService->events->delete($token->calendar_id, $blockedDate->google_event_id);
            }
        } catch (\Exception $e) {
            Log::error('Failed to remove blocked date from Google Calendar', [
                'blocked_date_id' => $blockedDate->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
