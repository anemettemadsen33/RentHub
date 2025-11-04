<?php

namespace App\Filament\Resources\CleaningSchedules\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class CleaningScheduleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('property_id')
                    ->relationship('property', 'title')
                    ->required(),
                Select::make('service_provider_id')
                    ->relationship('serviceProvider', 'name'),
                TextInput::make('created_by')
                    ->required()
                    ->numeric(),
                TextInput::make('schedule_type')
                    ->required()
                    ->default('recurring'),
                TextInput::make('frequency'),
                Textarea::make('days_of_week')
                    ->columnSpanFull(),
                TextInput::make('day_of_month')
                    ->numeric(),
                Textarea::make('custom_schedule')
                    ->columnSpanFull(),
                TimePicker::make('preferred_time')
                    ->required(),
                TextInput::make('duration_hours')
                    ->required()
                    ->numeric()
                    ->default(2),
                TextInput::make('service_type')
                    ->required()
                    ->default('regular_cleaning'),
                Textarea::make('cleaning_checklist')
                    ->columnSpanFull(),
                Textarea::make('special_instructions')
                    ->columnSpanFull(),
                DatePicker::make('start_date')
                    ->required(),
                DatePicker::make('end_date'),
                Toggle::make('active')
                    ->required(),
                DateTimePicker::make('last_executed_at'),
                DateTimePicker::make('next_execution_at'),
                Toggle::make('auto_book')
                    ->required(),
                TextInput::make('book_days_in_advance')
                    ->required()
                    ->numeric()
                    ->default(7),
                Toggle::make('notify_provider')
                    ->required(),
                Toggle::make('notify_owner')
                    ->required(),
                TextInput::make('reminder_hours_before')
                    ->required()
                    ->numeric()
                    ->default(24),
            ]);
    }
}
