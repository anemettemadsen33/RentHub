<x-filament-widgets::widget>
    <x-filament::section>
        <div class="space-y-4">
            {{-- Header with Month Navigation --}}
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold">
                    {{ $this->getMonthName() }}
                </h3>
                <div class="flex gap-2">
                    <x-filament::button
                        wire:click="previousMonth"
                        size="sm"
                        color="gray"
                    >
                        <x-heroicon-o-chevron-left class="w-4 h-4" />
                    </x-filament::button>
                    <x-filament::button
                        wire:click="nextMonth"
                        size="sm"
                        color="gray"
                    >
                        <x-heroicon-o-chevron-right class="w-4 h-4" />
                    </x-filament::button>
                </div>
            </div>

            {{-- Legend --}}
            <div class="flex gap-4 text-sm">
                <div class="flex items-center gap-2">
                    <div class="w-4 h-4 bg-green-500 rounded"></div>
                    <span>Available</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="w-4 h-4 bg-blue-500 rounded"></div>
                    <span>Booked</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="w-4 h-4 bg-red-500 rounded"></div>
                    <span>Blocked</span>
                </div>
            </div>

            {{-- Calendar for Each Property --}}
            @forelse($calendarData as $property)
                <div class="border rounded-lg p-4">
                    <h4 class="font-semibold mb-3">{{ $property['property_name'] }}</h4>
                    
                    {{-- Days of Week --}}
                    <div class="grid grid-cols-7 gap-2 mb-2 text-center text-sm font-medium">
                        <div>Sun</div>
                        <div>Mon</div>
                        <div>Tue</div>
                        <div>Wed</div>
                        <div>Thu</div>
                        <div>Fri</div>
                        <div>Sat</div>
                    </div>
                    
                    {{-- Calendar Days --}}
                    <div class="grid grid-cols-7 gap-2">
                        @php
                            $firstDay = \Carbon\Carbon::parse($currentMonth . '-01');
                            $startDay = $firstDay->copy()->startOfWeek(\Carbon\Carbon::SUNDAY);
                            $endDay = $firstDay->copy()->endOfMonth()->endOfWeek(\Carbon\Carbon::SUNDAY);
                            $current = $startDay->copy();
                        @endphp
                        
                        @while($current <= $endDay)
                            @php
                                $dateStr = $current->format('Y-m-d');
                                $isCurrentMonth = $current->month == $firstDay->month;
                                $dayData = $property['days'][$dateStr] ?? null;
                                
                                $bgColor = 'bg-gray-100';
                                $textColor = 'text-gray-400';
                                
                                if ($isCurrentMonth && $dayData) {
                                    switch($dayData['status']) {
                                        case 'available':
                                            $bgColor = 'bg-green-100 hover:bg-green-200';
                                            $textColor = 'text-green-900';
                                            break;
                                        case 'booked':
                                            $bgColor = 'bg-blue-100';
                                            $textColor = 'text-blue-900';
                                            break;
                                        case 'blocked':
                                            $bgColor = 'bg-red-100';
                                            $textColor = 'text-red-900';
                                            break;
                                    }
                                }
                            @endphp
                            
                            <div 
                                class="aspect-square p-2 rounded {{ $bgColor }} {{ $textColor }} text-center text-sm cursor-pointer transition-colors"
                                @if($dayData && $dayData['booking_id'])
                                    x-tooltip="{
                                        content: 'Guest: {{ $dayData['guest_name'] }}<br>Price: ${{ $dayData['price'] }}',
                                        theme: 'light',
                                        allowHTML: true
                                    }"
                                @endif
                            >
                                <div class="font-semibold">{{ $current->day }}</div>
                                @if($dayData && $isCurrentMonth)
                                    <div class="text-xs mt-1">${{ number_format($dayData['price'], 0) }}</div>
                                @endif
                            </div>
                            
                            @php $current->addDay(); @endphp
                        @endwhile
                    </div>
                </div>
            @empty
                <div class="text-center py-8 text-gray-500">
                    No properties found
                </div>
            @endforelse
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
