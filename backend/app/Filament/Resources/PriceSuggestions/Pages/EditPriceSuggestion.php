<?php

namespace App\Filament\Resources\PriceSuggestions\Pages;

use App\Filament\Resources\PriceSuggestions\PriceSuggestionResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPriceSuggestion extends EditRecord
{
    protected static string $resource = PriceSuggestionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
