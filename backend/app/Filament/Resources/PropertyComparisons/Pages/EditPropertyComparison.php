<?php

namespace App\Filament\Resources\PropertyComparisons\Pages;

use App\Filament\Resources\PropertyComparisons\PropertyComparisonResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditPropertyComparison extends EditRecord
{
    protected static string $resource = PropertyComparisonResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
