<?php

namespace App\Filament\Resources\SavedSearches\Pages;

use App\Filament\Resources\SavedSearches\SavedSearchResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSavedSearches extends ListRecords
{
    protected static string $resource = SavedSearchResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
