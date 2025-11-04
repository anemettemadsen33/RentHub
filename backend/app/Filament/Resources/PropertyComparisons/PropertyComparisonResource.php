<?php

namespace App\Filament\Resources\PropertyComparisons;

use App\Filament\Resources\PropertyComparisons\Pages\CreatePropertyComparison;
use App\Filament\Resources\PropertyComparisons\Pages\EditPropertyComparison;
use App\Filament\Resources\PropertyComparisons\Pages\ListPropertyComparisons;
use App\Filament\Resources\PropertyComparisons\Pages\ViewPropertyComparison;
use App\Filament\Resources\PropertyComparisons\Schemas\PropertyComparisonForm;
use App\Filament\Resources\PropertyComparisons\Schemas\PropertyComparisonInfolist;
use App\Filament\Resources\PropertyComparisons\Tables\PropertyComparisonsTable;
use App\Models\PropertyComparison;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PropertyComparisonResource extends Resource
{
    protected static ?string $model = PropertyComparison::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return PropertyComparisonForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return PropertyComparisonInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PropertyComparisonsTable::configure($table);
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
            'index' => ListPropertyComparisons::route('/'),
            'create' => CreatePropertyComparison::route('/create'),
            'view' => ViewPropertyComparison::route('/{record}'),
            'edit' => EditPropertyComparison::route('/{record}/edit'),
        ];
    }
}
