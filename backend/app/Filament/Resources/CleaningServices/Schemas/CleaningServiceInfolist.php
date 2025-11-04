<?php

namespace App\Filament\Resources\CleaningServices\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class CleaningServiceInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('property.title')
                    ->numeric(),
                TextEntry::make('booking.id')
                    ->numeric(),
                TextEntry::make('longTermRental.id')
                    ->numeric(),
                TextEntry::make('serviceProvider.name')
                    ->numeric(),
                TextEntry::make('requested_by')
                    ->numeric(),
                TextEntry::make('service_type'),
                TextEntry::make('scheduled_date')
                    ->dateTime(),
                TextEntry::make('scheduled_time')
                    ->time(),
                TextEntry::make('estimated_duration_hours')
                    ->numeric(),
                TextEntry::make('started_at')
                    ->dateTime(),
                TextEntry::make('completed_at')
                    ->dateTime(),
                IconEntry::make('requires_key')
                    ->boolean(),
                TextEntry::make('access_code'),
                TextEntry::make('status'),
                TextEntry::make('cancelled_at')
                    ->dateTime(),
                TextEntry::make('rating')
                    ->numeric(),
                TextEntry::make('rated_at')
                    ->dateTime(),
                TextEntry::make('estimated_cost')
                    ->numeric(),
                TextEntry::make('actual_cost')
                    ->numeric(),
                TextEntry::make('payment_status'),
                TextEntry::make('paid_at')
                    ->dateTime(),
                IconEntry::make('provider_brings_supplies')
                    ->boolean(),
                TextEntry::make('created_at')
                    ->dateTime(),
                TextEntry::make('updated_at')
                    ->dateTime(),
                TextEntry::make('deleted_at')
                    ->dateTime(),
            ]);
    }
}
