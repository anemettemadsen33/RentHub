<?php

namespace App\Filament\Resources\ConciergeBookings\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ConciergeBookingInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('user.name')
                    ->numeric(),
                TextEntry::make('property.title')
                    ->numeric(),
                TextEntry::make('booking.id')
                    ->numeric(),
                TextEntry::make('conciergeService.name')
                    ->numeric(),
                TextEntry::make('booking_reference'),
                TextEntry::make('service_date')
                    ->dateTime(),
                TextEntry::make('service_time')
                    ->time(),
                TextEntry::make('guests_count')
                    ->numeric(),
                TextEntry::make('base_price')
                    ->numeric(),
                TextEntry::make('extras_price')
                    ->numeric(),
                TextEntry::make('total_price')
                    ->numeric(),
                TextEntry::make('currency'),
                TextEntry::make('status'),
                TextEntry::make('payment_status'),
                TextEntry::make('payment.id')
                    ->numeric(),
                TextEntry::make('confirmed_at')
                    ->dateTime(),
                TextEntry::make('started_at')
                    ->dateTime(),
                TextEntry::make('completed_at')
                    ->dateTime(),
                TextEntry::make('cancelled_at')
                    ->dateTime(),
                TextEntry::make('rating')
                    ->numeric(),
                TextEntry::make('reviewed_at')
                    ->dateTime(),
                TextEntry::make('created_at')
                    ->dateTime(),
                TextEntry::make('updated_at')
                    ->dateTime(),
                TextEntry::make('deleted_at')
                    ->dateTime(),
            ]);
    }
}
