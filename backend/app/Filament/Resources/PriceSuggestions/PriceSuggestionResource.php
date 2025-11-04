<?php

namespace App\Filament\Resources\PriceSuggestions;

use App\Filament\Resources\PriceSuggestions\Pages\CreatePriceSuggestion;
use App\Filament\Resources\PriceSuggestions\Pages\EditPriceSuggestion;
use App\Filament\Resources\PriceSuggestions\Pages\ListPriceSuggestions;
use App\Filament\Resources\PriceSuggestions\Schemas\PriceSuggestionForm;
use App\Filament\Resources\PriceSuggestions\Tables\PriceSuggestionsTable;
use App\Models\PriceSuggestion;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PriceSuggestionResource extends Resource
{
    protected static ?string $model = PriceSuggestion::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'property_id';

    public static function form(Schema $schema): Schema
    {
        return PriceSuggestionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PriceSuggestionsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPriceSuggestions::route('/'),
            'create' => CreatePriceSuggestion::route('/create'),
            'edit' => EditPriceSuggestion::route('/{record}/edit'),
        ];
    }
}
