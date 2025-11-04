<?php

namespace App\Filament\Resources\PropertyComparisons\Pages;

use App\Filament\Resources\PropertyComparisons\PropertyComparisonResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewPropertyComparison extends ViewRecord
{
    protected static string $resource = PropertyComparisonResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
