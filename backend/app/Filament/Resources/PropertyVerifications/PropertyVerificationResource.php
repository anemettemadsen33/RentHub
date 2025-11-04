<?php

namespace App\Filament\Resources\PropertyVerifications;

use App\Filament\Resources\PropertyVerifications\Pages\CreatePropertyVerification;
use App\Filament\Resources\PropertyVerifications\Pages\EditPropertyVerification;
use App\Filament\Resources\PropertyVerifications\Pages\ListPropertyVerifications;
use App\Filament\Resources\PropertyVerifications\Pages\ViewPropertyVerification;
use App\Filament\Resources\PropertyVerifications\Schemas\PropertyVerificationForm;
use App\Filament\Resources\PropertyVerifications\Schemas\PropertyVerificationInfolist;
use App\Filament\Resources\PropertyVerifications\Tables\PropertyVerificationsTable;
use App\Models\PropertyVerification;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PropertyVerificationResource extends Resource
{
    protected static ?string $model = PropertyVerification::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return PropertyVerificationForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return PropertyVerificationInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PropertyVerificationsTable::configure($table);
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
            'index' => ListPropertyVerifications::route('/'),
            'create' => CreatePropertyVerification::route('/create'),
            'view' => ViewPropertyVerification::route('/{record}'),
            'edit' => EditPropertyVerification::route('/{record}/edit'),
        ];
    }
}
