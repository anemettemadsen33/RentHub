<?php

namespace App\Filament\Resources\ConciergeServices\Pages;

use App\Filament\Resources\ConciergeServices\ConciergeServiceResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListConciergeServices extends ListRecords
{
    protected static string $resource = ConciergeServiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
