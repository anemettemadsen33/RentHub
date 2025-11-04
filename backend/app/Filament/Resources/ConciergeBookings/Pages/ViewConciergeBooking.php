<?php

namespace App\Filament\Resources\ConciergeBookings\Pages;

use App\Filament\Resources\ConciergeBookings\ConciergeBookingResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewConciergeBooking extends ViewRecord
{
    protected static string $resource = ConciergeBookingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
