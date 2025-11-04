<?php

namespace App\Filament\Resources\LongTermRentals\Pages;

use App\Filament\Resources\LongTermRentals\LongTermRentalResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewLongTermRental extends ViewRecord
{
    protected static string $resource = LongTermRentalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
