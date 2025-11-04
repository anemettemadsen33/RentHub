<?php

namespace App\Filament\Resources\LongTermRentals\Pages;

use App\Filament\Resources\LongTermRentals\LongTermRentalResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListLongTermRentals extends ListRecords
{
    protected static string $resource = LongTermRentalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
