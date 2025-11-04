<?php

namespace App\Filament\Resources\ConciergeBookings;

use App\Enums\NavigationGroup;
use App\Filament\Resources\ConciergeBookings\Pages\CreateConciergeBooking;
use App\Filament\Resources\ConciergeBookings\Pages\EditConciergeBooking;
use App\Filament\Resources\ConciergeBookings\Pages\ListConciergeBookings;
use App\Filament\Resources\ConciergeBookings\Pages\ViewConciergeBooking;
use App\Filament\Resources\ConciergeBookings\Schemas\ConciergeBookingForm;
use App\Filament\Resources\ConciergeBookings\Schemas\ConciergeBookingInfolist;
use App\Filament\Resources\ConciergeBookings\Tables\ConciergeBookingsTable;
use App\Models\ConciergeBooking;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class ConciergeBookingResource extends Resource
{
    protected static ?string $model = ConciergeBooking::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendar;

    protected static ?string $navigationLabel = 'Bookings';

    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::CONCIERGE;

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'booking_reference';

    public static function form(Schema $schema): Schema
    {
        return ConciergeBookingForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ConciergeBookingInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ConciergeBookingsTable::configure($table);
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
            'index' => ListConciergeBookings::route('/'),
            'create' => CreateConciergeBooking::route('/create'),
            'view' => ViewConciergeBooking::route('/{record}'),
            'edit' => EditConciergeBooking::route('/{record}/edit'),
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
