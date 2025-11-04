<?php

namespace App\Filament\Resources\ExternalCalendars;

use App\Filament\Resources\ExternalCalendars\Pages\CreateExternalCalendar;
use App\Filament\Resources\ExternalCalendars\Pages\EditExternalCalendar;
use App\Filament\Resources\ExternalCalendars\Pages\ListExternalCalendars;
use App\Filament\Resources\ExternalCalendars\Pages\ViewExternalCalendar;
use App\Filament\Resources\ExternalCalendars\Schemas\ExternalCalendarForm;
use App\Filament\Resources\ExternalCalendars\Schemas\ExternalCalendarInfolist;
use App\Filament\Resources\ExternalCalendars\Tables\ExternalCalendarsTable;
use App\Models\ExternalCalendar;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ExternalCalendarResource extends Resource
{
    protected static ?string $model = ExternalCalendar::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendar;

    protected static ?string $navigationLabel = 'External Calendars';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return ExternalCalendarForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ExternalCalendarInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ExternalCalendarsTable::configure($table);
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
            'index' => ListExternalCalendars::route('/'),
            'create' => CreateExternalCalendar::route('/create'),
            'view' => ViewExternalCalendar::route('/{record}'),
            'edit' => EditExternalCalendar::route('/{record}/edit'),
        ];
    }
}
