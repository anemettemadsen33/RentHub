<?php

namespace App\Filament\Resources\ConciergeServices\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ConciergeServiceInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('serviceProvider.name')
                    ->numeric(),
                TextEntry::make('name'),
                TextEntry::make('service_type'),
                TextEntry::make('base_price')
                    ->numeric(),
                TextEntry::make('price_unit'),
                TextEntry::make('duration_minutes')
                    ->numeric(),
                TextEntry::make('max_guests')
                    ->numeric(),
                IconEntry::make('is_available')
                    ->boolean(),
                TextEntry::make('advance_booking_hours')
                    ->numeric(),
                TextEntry::make('created_at')
                    ->dateTime(),
                TextEntry::make('updated_at')
                    ->dateTime(),
                TextEntry::make('deleted_at')
                    ->dateTime(),
            ]);
    }
}
