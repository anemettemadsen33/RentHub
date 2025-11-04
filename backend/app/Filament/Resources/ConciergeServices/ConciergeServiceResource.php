<?php

namespace App\Filament\Resources\ConciergeServices;

use App\Enums\NavigationGroup;
use App\Filament\Resources\ConciergeServices\Pages\CreateConciergeService;
use App\Filament\Resources\ConciergeServices\Pages\EditConciergeService;
use App\Filament\Resources\ConciergeServices\Pages\ListConciergeServices;
use App\Filament\Resources\ConciergeServices\Pages\ViewConciergeService;
use App\Filament\Resources\ConciergeServices\Schemas\ConciergeServiceForm;
use App\Filament\Resources\ConciergeServices\Schemas\ConciergeServiceInfolist;
use App\Filament\Resources\ConciergeServices\Tables\ConciergeServicesTable;
use App\Models\ConciergeService;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class ConciergeServiceResource extends Resource
{
    protected static ?string $model = ConciergeService::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSparkles;

    protected static ?string $navigationLabel = 'Services';

    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::CONCIERGE;

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return ConciergeServiceForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ConciergeServiceInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ConciergeServicesTable::configure($table);
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
            'index' => ListConciergeServices::route('/'),
            'create' => CreateConciergeService::route('/create'),
            'view' => ViewConciergeService::route('/{record}'),
            'edit' => EditConciergeService::route('/{record}/edit'),
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
