<?php

namespace App\Filament\Resources\CleaningSchedules;

use App\Filament\Resources\CleaningSchedules\Pages\CreateCleaningSchedule;
use App\Filament\Resources\CleaningSchedules\Pages\EditCleaningSchedule;
use App\Filament\Resources\CleaningSchedules\Pages\ListCleaningSchedules;
use App\Filament\Resources\CleaningSchedules\Pages\ViewCleaningSchedule;
use App\Filament\Resources\CleaningSchedules\Schemas\CleaningScheduleForm;
use App\Filament\Resources\CleaningSchedules\Schemas\CleaningScheduleInfolist;
use App\Filament\Resources\CleaningSchedules\Tables\CleaningSchedulesTable;
use App\Models\CleaningSchedule;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CleaningScheduleResource extends Resource
{
    protected static ?string $model = CleaningSchedule::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'schedule_type';

    public static function form(Schema $schema): Schema
    {
        return CleaningScheduleForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return CleaningScheduleInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CleaningSchedulesTable::configure($table);
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
            'index' => ListCleaningSchedules::route('/'),
            'create' => CreateCleaningSchedule::route('/create'),
            'view' => ViewCleaningSchedule::route('/{record}'),
            'edit' => EditCleaningSchedule::route('/{record}/edit'),
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
