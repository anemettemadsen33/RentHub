<?php

namespace App\Filament\Resources\ConciergeBookings\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Schemas\Schema;

class ConciergeBookingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required(),
                Select::make('property_id')
                    ->relationship('property', 'title'),
                Select::make('booking_id')
                    ->relationship('booking', 'id'),
                Select::make('concierge_service_id')
                    ->relationship('conciergeService', 'name')
                    ->required(),
                TextInput::make('booking_reference')
                    ->required(),
                DateTimePicker::make('service_date')
                    ->required(),
                TimePicker::make('service_time'),
                TextInput::make('guests_count')
                    ->required()
                    ->numeric()
                    ->default(1),
                Textarea::make('special_requests')
                    ->columnSpanFull(),
                TextInput::make('base_price')
                    ->required()
                    ->numeric(),
                TextInput::make('extras_price')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('total_price')
                    ->required()
                    ->numeric(),
                TextInput::make('currency')
                    ->required()
                    ->default('USD'),
                TextInput::make('status')
                    ->required()
                    ->default('pending'),
                TextInput::make('payment_status')
                    ->required()
                    ->default('pending'),
                Select::make('payment_id')
                    ->relationship('payment', 'id'),
                DateTimePicker::make('confirmed_at'),
                DateTimePicker::make('started_at'),
                DateTimePicker::make('completed_at'),
                DateTimePicker::make('cancelled_at'),
                Textarea::make('cancellation_reason')
                    ->columnSpanFull(),
                TextInput::make('rating')
                    ->numeric(),
                Textarea::make('review')
                    ->columnSpanFull(),
                DateTimePicker::make('reviewed_at'),
                Textarea::make('contact_details')
                    ->columnSpanFull(),
            ]);
    }
}
