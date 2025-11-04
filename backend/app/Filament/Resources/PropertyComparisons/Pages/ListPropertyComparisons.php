<?php

namespace App\Filament\Resources\PropertyComparisons\Pages;

use App\Filament\Resources\PropertyComparisons\PropertyComparisonResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPropertyComparisons extends ListRecords
{
    protected static string $resource = PropertyComparisonResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
