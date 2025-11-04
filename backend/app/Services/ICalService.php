<?php

namespace App\Services;

use App\Models\ExternalCalendar;
use App\Models\Property;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ICalService
{
    /**
     * Generate iCal feed for a property
     */
    public function generateFeed(Property $property): string
    {
        $blockedDates = $property->blocked_dates ?? [];
        $bookings = $property->bookings()
            ->whereIn('status', ['confirmed', 'checked_in'])
            ->get();

        $ical = "BEGIN:VCALENDAR\r\n";
        $ical .= "VERSION:2.0\r\n";
        $ical .= "PRODID:-//RentHub//Calendar//EN\r\n";
        $ical .= "CALSCALE:GREGORIAN\r\n";
        $ical .= "METHOD:PUBLISH\r\n";
        $ical .= "X-WR-CALNAME:{$property->title}\r\n";
        $ical .= "X-WR-TIMEZONE:UTC\r\n";

        // Add blocked dates as all-day events
        foreach ($blockedDates as $date) {
            $ical .= $this->createEvent(
                'blocked_'.$date,
                'Blocked',
                $date,
                Carbon::parse($date)->addDay()->format('Y-m-d'),
                'Property blocked by owner'
            );
        }

        // Add bookings
        foreach ($bookings as $booking) {
            $ical .= $this->createEvent(
                'booking_'.$booking->id,
                'Booked - '.$booking->guest_name,
                $booking->check_in,
                $booking->check_out,
                "Guest: {$booking->guest_name}\nStatus: {$booking->status}"
            );
        }

        $ical .= "END:VCALENDAR\r\n";

        return $ical;
    }

    /**
     * Create iCal event string
     */
    private function createEvent(string $uid, string $summary, string $startDate, string $endDate, string $description = ''): string
    {
        $start = Carbon::parse($startDate)->format('Ymd');
        $end = Carbon::parse($endDate)->format('Ymd');
        $now = Carbon::now()->format('Ymd\THis\Z');

        $event = "BEGIN:VEVENT\r\n";
        $event .= "UID:{$uid}@renthub.com\r\n";
        $event .= "DTSTAMP:{$now}\r\n";
        $event .= "DTSTART;VALUE=DATE:{$start}\r\n";
        $event .= "DTEND;VALUE=DATE:{$end}\r\n";
        $event .= "SUMMARY:{$summary}\r\n";
        if ($description) {
            $event .= 'DESCRIPTION:'.$this->escapeString($description)."\r\n";
        }
        $event .= "STATUS:CONFIRMED\r\n";
        $event .= "TRANSP:OPAQUE\r\n";
        $event .= "END:VEVENT\r\n";

        return $event;
    }

    /**
     * Escape special characters for iCal
     */
    private function escapeString(string $text): string
    {
        $text = str_replace(['\\', ',', ';', "\n"], ['\\\\', '\\,', '\\;', '\\n'], $text);

        return $text;
    }

    /**
     * Import events from external iCal URL
     */
    public function importFromUrl(string $url): array
    {
        try {
            $response = Http::timeout(30)->get($url);

            if (! $response->successful()) {
                throw new \Exception('Failed to fetch iCal feed: '.$response->status());
            }

            $icalData = $response->body();

            return $this->parseICalData($icalData);
        } catch (\Exception $e) {
            Log::error('iCal import failed', [
                'url' => $url,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Parse iCal data and extract blocked dates
     */
    public function parseICalData(string $icalData): array
    {
        $events = [];
        $lines = explode("\n", str_replace("\r\n", "\n", $icalData));
        $currentEvent = null;

        foreach ($lines as $line) {
            $line = trim($line);

            if (strpos($line, 'BEGIN:VEVENT') === 0) {
                $currentEvent = [
                    'summary' => '',
                    'start' => null,
                    'end' => null,
                    'description' => '',
                ];
            } elseif (strpos($line, 'END:VEVENT') === 0 && $currentEvent) {
                if ($currentEvent['start'] && $currentEvent['end']) {
                    $events[] = $currentEvent;
                }
                $currentEvent = null;
            } elseif ($currentEvent) {
                if (strpos($line, 'DTSTART') === 0) {
                    $currentEvent['start'] = $this->parseICalDate($line);
                } elseif (strpos($line, 'DTEND') === 0) {
                    $currentEvent['end'] = $this->parseICalDate($line);
                } elseif (strpos($line, 'SUMMARY:') === 0) {
                    $currentEvent['summary'] = substr($line, 8);
                } elseif (strpos($line, 'DESCRIPTION:') === 0) {
                    $currentEvent['description'] = substr($line, 12);
                }
            }
        }

        return $events;
    }

    /**
     * Parse iCal date format
     */
    private function parseICalDate(string $line): ?string
    {
        // Extract date value from line like "DTSTART;VALUE=DATE:20250101" or "DTSTART:20250101T000000Z"
        if (preg_match('/:(\d{8})(T\d{6}Z?)?/', $line, $matches)) {
            $dateStr = $matches[1];
            try {
                return Carbon::createFromFormat('Ymd', $dateStr)->format('Y-m-d');
            } catch (\Exception $e) {
                Log::warning('Failed to parse iCal date', ['line' => $line]);

                return null;
            }
        }

        return null;
    }

    /**
     * Get date range from events
     */
    public function getBlockedDatesFromEvents(array $events): array
    {
        $blockedDates = [];

        foreach ($events as $event) {
            if (! $event['start'] || ! $event['end']) {
                continue;
            }

            try {
                $startDate = Carbon::parse($event['start']);
                $endDate = Carbon::parse($event['end']);

                // Generate all dates in the range
                while ($startDate->lt($endDate)) {
                    $blockedDates[] = $startDate->format('Y-m-d');
                    $startDate->addDay();
                }
            } catch (\Exception $e) {
                Log::warning('Failed to process event dates', [
                    'event' => $event,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return array_unique($blockedDates);
    }

    /**
     * Sync external calendar to property
     */
    public function syncExternalCalendar(ExternalCalendar $externalCalendar): array
    {
        try {
            // Import events from URL
            $events = $this->importFromUrl($externalCalendar->url);
            $newBlockedDates = $this->getBlockedDatesFromEvents($events);

            // Get property
            $property = $externalCalendar->property;
            $currentBlockedDates = $property->blocked_dates ?? [];

            // Calculate changes
            $datesToAdd = array_diff($newBlockedDates, $currentBlockedDates);
            $datesToRemove = []; // We don't remove dates from external sources by default

            // Add new blocked dates
            $addedCount = 0;
            foreach ($datesToAdd as $date) {
                if ($property->blockDate($date)) {
                    $addedCount++;
                }
            }

            // Update sync status
            $externalCalendar->update([
                'last_synced_at' => now(),
                'sync_error' => null,
            ]);

            return [
                'success' => true,
                'dates_added' => $addedCount,
                'dates_removed' => 0,
                'total_events' => count($events),
                'total_blocked_dates' => count($newBlockedDates),
            ];
        } catch (\Exception $e) {
            // Update sync error
            $externalCalendar->update([
                'sync_error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
}
