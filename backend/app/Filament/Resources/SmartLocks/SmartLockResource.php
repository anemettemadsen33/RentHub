<?php

namespace App\Filament\Resources\SmartLocks;

use App\Filament\Resources\SmartLocks\Pages\CreateSmartLock;
use App\Filament\Resources\SmartLocks\Pages\EditSmartLock;
use App\Filament\Resources\SmartLocks\Pages\ListSmartLocks;
use App\Filament\Resources\SmartLocks\Pages\ViewSmartLock;
use App\Filament\Resources\SmartLocks\Schemas\SmartLockForm;
use App\Filament\Resources\SmartLocks\Schemas\SmartLockInfolist;
use App\Filament\Resources\SmartLocks\Tables\SmartLocksTable;
use App\Models\SmartLock;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SmartLockResource extends Resource
{
    protected static ?string $model = SmartLock::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return SmartLockForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return SmartLockInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SmartLocksTable::configure($table);
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
            'index' => ListSmartLocks::route('/'),
            'create' => CreateSmartLock::route('/create'),
            'view' => ViewSmartLock::route('/{record}'),
            'edit' => EditSmartLock::route('/{record}/edit'),
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
