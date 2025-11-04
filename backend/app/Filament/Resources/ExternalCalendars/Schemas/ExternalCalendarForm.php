<?php

namespace App\Filament\Resources\ExternalCalendars\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ExternalCalendarForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Select::make('property_id')
                    ->relationship('property', 'title')
                    ->required()
                    ->searchable()
                    ->preload(),

                Select::make('platform')
                    ->options([
                        'airbnb' => 'Airbnb',
                        'booking' => 'Booking.com',
                        'vrbo' => 'VRBO',
                        'google' => 'Google Calendar',
                        'ical' => 'iCal (Generic)',
                    ])
                    ->required()
                    ->native(false),

                TextInput::make('name')
                    ->label('Calendar Name')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('e.g., My Airbnb Listing'),

                Toggle::make('sync_enabled')
                    ->label('Enable Automatic Sync')
                    ->default(true)
                    ->helperText('Sync every 6 hours automatically'),

                TextInput::make('url')
                    ->label('iCal URL')
                    ->required()
                    ->url()
                    ->maxLength(500)
                    ->placeholder('https://...')
                    ->helperText('Paste the iCal export URL from external platform')
                    ->columnSpanFull(),

                DateTimePicker::make('last_synced_at')
                    ->label('Last Synced')
                    ->disabled()
                    ->dehydrated(false),

                Textarea::make('sync_error')
                    ->label('Sync Error (if any)')
                    ->disabled()
                    ->dehydrated(false)
                    ->rows(3)
                    ->columnSpanFull(),
            ]);
    }
}
