<?php

namespace App\Filament\Resources\SavedSearches\Pages;

use App\Filament\Resources\SavedSearches\SavedSearchResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSavedSearch extends EditRecord
{
    protected static string $resource = SavedSearchResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
