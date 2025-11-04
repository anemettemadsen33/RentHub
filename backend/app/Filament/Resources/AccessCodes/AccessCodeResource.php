<?php

namespace App\Filament\Resources\AccessCodes;

use App\Filament\Resources\AccessCodes\Pages\CreateAccessCode;
use App\Filament\Resources\AccessCodes\Pages\EditAccessCode;
use App\Filament\Resources\AccessCodes\Pages\ListAccessCodes;
use App\Filament\Resources\AccessCodes\Pages\ViewAccessCode;
use App\Filament\Resources\AccessCodes\Schemas\AccessCodeForm;
use App\Filament\Resources\AccessCodes\Schemas\AccessCodeInfolist;
use App\Filament\Resources\AccessCodes\Tables\AccessCodesTable;
use App\Models\AccessCode;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AccessCodeResource extends Resource
{
    protected static ?string $model = AccessCode::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'code';

    public static function form(Schema $schema): Schema
    {
        return AccessCodeForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return AccessCodeInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AccessCodesTable::configure($table);
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
            'index' => ListAccessCodes::route('/'),
            'create' => CreateAccessCode::route('/create'),
            'view' => ViewAccessCode::route('/{record}'),
            'edit' => EditAccessCode::route('/{record}/edit'),
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
