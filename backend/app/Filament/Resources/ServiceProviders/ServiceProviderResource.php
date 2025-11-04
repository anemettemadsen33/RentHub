<?php

namespace App\Filament\Resources\ServiceProviders;

use App\Filament\Resources\ServiceProviders\Pages\CreateServiceProvider;
use App\Filament\Resources\ServiceProviders\Pages\EditServiceProvider;
use App\Filament\Resources\ServiceProviders\Pages\ListServiceProviders;
use App\Filament\Resources\ServiceProviders\Pages\ViewServiceProvider;
use App\Filament\Resources\ServiceProviders\Schemas\ServiceProviderForm;
use App\Filament\Resources\ServiceProviders\Schemas\ServiceProviderInfolist;
use App\Filament\Resources\ServiceProviders\Tables\ServiceProvidersTable;
use App\Models\ServiceProvider;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ServiceProviderResource extends Resource
{
    protected static ?string $model = ServiceProvider::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return ServiceProviderForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ServiceProviderInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ServiceProvidersTable::configure($table);
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
            'index' => ListServiceProviders::route('/'),
            'create' => CreateServiceProvider::route('/create'),
            'view' => ViewServiceProvider::route('/{record}'),
            'edit' => EditServiceProvider::route('/{record}/edit'),
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
