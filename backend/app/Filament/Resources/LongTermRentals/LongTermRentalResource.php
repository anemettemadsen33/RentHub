<?php

namespace App\Filament\Resources\LongTermRentals;

use App\Filament\Resources\LongTermRentals\Pages\CreateLongTermRental;
use App\Filament\Resources\LongTermRentals\Pages\EditLongTermRental;
use App\Filament\Resources\LongTermRentals\Pages\ListLongTermRentals;
use App\Filament\Resources\LongTermRentals\Pages\ViewLongTermRental;
use App\Filament\Resources\LongTermRentals\Schemas\LongTermRentalForm;
use App\Filament\Resources\LongTermRentals\Schemas\LongTermRentalInfolist;
use App\Filament\Resources\LongTermRentals\Tables\LongTermRentalsTable;
use App\Models\LongTermRental;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class LongTermRentalResource extends Resource
{
    protected static ?string $model = LongTermRental::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return LongTermRentalForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return LongTermRentalInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LongTermRentalsTable::configure($table);
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
            'index' => ListLongTermRentals::route('/'),
            'create' => CreateLongTermRental::route('/create'),
            'view' => ViewLongTermRental::route('/{record}'),
            'edit' => EditLongTermRental::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
