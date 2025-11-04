<?php

namespace App\Filament\Resources\SavedSearches;

use App\Filament\Resources\SavedSearches\Pages\CreateSavedSearch;
use App\Filament\Resources\SavedSearches\Pages\EditSavedSearch;
use App\Filament\Resources\SavedSearches\Pages\ListSavedSearches;
use App\Filament\Resources\SavedSearches\Pages\ViewSavedSearch;
use App\Models\SavedSearch;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class SavedSearchResource extends Resource
{
    protected static ?string $model = SavedSearch::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMagnifyingGlass;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return \App\Filament\Resources\SavedSearches\Schemas\SavedSearchForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return \App\Filament\Resources\SavedSearches\Tables\SavedSearchesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSavedSearches::route('/'),
            'create' => CreateSavedSearch::route('/create'),
            'view' => ViewSavedSearch::route('/{record}'),
            'edit' => EditSavedSearch::route('/{record}/edit'),
        ];
    }
}
