<?php

namespace App\Filament\Widgets;

use App\Models\Property;
use App\Models\Booking;
use App\Models\BlockedDate;
use Carbon\Carbon;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;

class CalendarWidget extends Widget
{
    protected string $view = 'filament.widgets.calendar-widget';
    
    protected int | string | array $columnSpan = 'full';
    
    public ?int $propertyId = null;
    public string $currentMonth;
    public array $calendarData = [];
    
    public function mount(?int $propertyId = null): void
    {
        $this->propertyId = $propertyId;
        $this->currentMonth = now()->format('Y-m');
        $this->loadCalendarData();
    }
    
    public function loadCalendarData(): void
    {
        $startDate = Carbon::parse($this->currentMonth . '-01')->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();
        
        $query = Property::query();
        
        if ($this->propertyId) {
            $query->where('id', $this->propertyId);
        } elseif (!Auth::user()->hasRole('super_admin')) {
            $query->where('user_id', Auth::id());
        }
        
        $properties = $query->with([
            'bookings' => function ($q) use ($startDate, $endDate) {
                $q->where(function ($query) use ($startDate, $endDate) {
                    $query->whereBetween('check_in', [$startDate, $endDate])
                        ->orWhereBetween('check_out', [$startDate, $endDate])
                        ->orWhere(function ($q) use ($startDate, $endDate) {
                            $q->where('check_in', '<=', $startDate)
                              ->where('check_out', '>=', $endDate);
                        });
                })->whereIn('status', ['confirmed', 'checked_in', 'checked_out']);
            },
            'blockedDates' => function ($q) use ($startDate, $endDate) {
                $q->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')]);
            }
        ])->get();
        
        $this->calendarData = $properties->map(function ($property) use ($startDate, $endDate) {
            $days = [];
            $current = $startDate->copy();
            
            while ($current <= $endDate) {
                $dateStr = $current->format('Y-m-d');
                
                // Check for bookings
                $booking = $property->bookings->first(function ($b) use ($dateStr) {
                    return $dateStr >= $b->check_in && $dateStr <= $b->check_out;
                });
                
                // Check for blocked dates
                $isBlocked = $property->blockedDates->contains('date', $dateStr);
                
                $days[$dateStr] = [
                    'date' => $dateStr,
                    'status' => $booking ? 'booked' : ($isBlocked ? 'blocked' : 'available'),
                    'booking_id' => $booking ? $booking->id : null,
                    'guest_name' => $booking ? $booking->user->name : null,
                    'price' => $property->customPrices->firstWhere('date', $dateStr)?->price ?? $property->price_per_night
                ];
                
                $current->addDay();
            }
            
            return [
                'property_id' => $property->id,
                'property_name' => $property->title,
                'days' => $days
            ];
        })->toArray();
    }
    
    public function previousMonth(): void
    {
        $this->currentMonth = Carbon::parse($this->currentMonth . '-01')
            ->subMonth()
            ->format('Y-m');
        $this->loadCalendarData();
    }
    
    public function nextMonth(): void
    {
        $this->currentMonth = Carbon::parse($this->currentMonth . '-01')
            ->addMonth()
            ->format('Y-m');
        $this->loadCalendarData();
    }
    
    public function getMonthName(): string
    {
        return Carbon::parse($this->currentMonth . '-01')->format('F Y');
    }
}
