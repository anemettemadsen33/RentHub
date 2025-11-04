<?php

namespace App\Filament\Resources\LockActivities;

use App\Filament\Resources\LockActivities\Pages\CreateLockActivity;
use App\Filament\Resources\LockActivities\Pages\EditLockActivity;
use App\Filament\Resources\LockActivities\Pages\ListLockActivities;
use App\Filament\Resources\LockActivities\Schemas\LockActivityForm;
use App\Filament\Resources\LockActivities\Tables\LockActivitiesTable;
use App\Models\LockActivity;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class LockActivityResource extends Resource
{
    protected static ?string $model = LockActivity::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'event_type';

    public static function form(Schema $schema): Schema
    {
        return LockActivityForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LockActivitiesTable::configure($table);
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
            'index' => ListLockActivities::route('/'),
            'create' => CreateLockActivity::route('/create'),
            'edit' => EditLockActivity::route('/{record}/edit'),
        ];
    }
}
