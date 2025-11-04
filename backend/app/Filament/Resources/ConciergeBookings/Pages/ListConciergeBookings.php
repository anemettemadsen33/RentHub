<?php

namespace App\Filament\Resources\ConciergeBookings\Pages;

use App\Filament\Resources\ConciergeBookings\ConciergeBookingResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListConciergeBookings extends ListRecords
{
    protected static string $resource = ConciergeBookingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
