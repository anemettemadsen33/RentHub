<?php

namespace App\Filament\Resources\CleaningServices\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class CleaningServiceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('property_id')
                    ->relationship('property', 'title')
                    ->required(),
                Select::make('booking_id')
                    ->relationship('booking', 'id'),
                Select::make('long_term_rental_id')
                    ->relationship('longTermRental', 'id'),
                Select::make('service_provider_id')
                    ->relationship('serviceProvider', 'name'),
                TextInput::make('requested_by')
                    ->required()
                    ->numeric(),
                TextInput::make('service_type')
                    ->required()
                    ->default('regular_cleaning'),
                Textarea::make('description')
                    ->columnSpanFull(),
                Textarea::make('checklist')
                    ->columnSpanFull(),
                Textarea::make('special_instructions')
                    ->columnSpanFull(),
                DateTimePicker::make('scheduled_date')
                    ->required(),
                TimePicker::make('scheduled_time'),
                TextInput::make('estimated_duration_hours')
                    ->required()
                    ->numeric()
                    ->default(2),
                DateTimePicker::make('started_at'),
                DateTimePicker::make('completed_at'),
                Toggle::make('requires_key')
                    ->required(),
                Textarea::make('access_instructions')
                    ->columnSpanFull(),
                TextInput::make('access_code'),
                TextInput::make('status')
                    ->required()
                    ->default('scheduled'),
                Textarea::make('cancellation_reason')
                    ->columnSpanFull(),
                DateTimePicker::make('cancelled_at'),
                Textarea::make('completed_checklist')
                    ->columnSpanFull(),
                Textarea::make('before_photos')
                    ->columnSpanFull(),
                Textarea::make('after_photos')
                    ->columnSpanFull(),
                Textarea::make('completion_notes')
                    ->columnSpanFull(),
                Textarea::make('issues_found')
                    ->columnSpanFull(),
                TextInput::make('rating')
                    ->numeric(),
                Textarea::make('feedback')
                    ->columnSpanFull(),
                DateTimePicker::make('rated_at'),
                TextInput::make('estimated_cost')
                    ->numeric(),
                TextInput::make('actual_cost')
                    ->numeric(),
                TextInput::make('payment_status')
                    ->required()
                    ->default('pending'),
                DateTimePicker::make('paid_at'),
                Toggle::make('provider_brings_supplies')
                    ->required(),
                Textarea::make('supplies_needed')
                    ->columnSpanFull(),
            ]);
    }
}
