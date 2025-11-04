<?php

namespace App\Filament\Resources\CleaningServices;

use App\Filament\Resources\CleaningServices\Pages\CreateCleaningService;
use App\Filament\Resources\CleaningServices\Pages\EditCleaningService;
use App\Filament\Resources\CleaningServices\Pages\ListCleaningServices;
use App\Filament\Resources\CleaningServices\Pages\ViewCleaningService;
use App\Filament\Resources\CleaningServices\Schemas\CleaningServiceForm;
use App\Filament\Resources\CleaningServices\Schemas\CleaningServiceInfolist;
use App\Filament\Resources\CleaningServices\Tables\CleaningServicesTable;
use App\Models\CleaningService;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CleaningServiceResource extends Resource
{
    protected static ?string $model = CleaningService::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'service_type';

    public static function form(Schema $schema): Schema
    {
        return CleaningServiceForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return CleaningServiceInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CleaningServicesTable::configure($table);
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
            'index' => ListCleaningServices::route('/'),
            'create' => CreateCleaningService::route('/create'),
            'view' => ViewCleaningService::route('/{record}'),
            'edit' => EditCleaningService::route('/{record}/edit'),
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
