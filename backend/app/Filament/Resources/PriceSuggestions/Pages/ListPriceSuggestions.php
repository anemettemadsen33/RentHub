<?php

namespace App\Filament\Resources\PriceSuggestions\Pages;

use App\Filament\Resources\PriceSuggestions\PriceSuggestionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPriceSuggestions extends ListRecords
{
    protected static string $resource = PriceSuggestionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
