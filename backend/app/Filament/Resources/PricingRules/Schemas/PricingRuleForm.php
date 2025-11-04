<?php

namespace App\Filament\Resources\PricingRules\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class PricingRuleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('property_id')
                    ->relationship('property', 'title')
                    ->required(),
                TextInput::make('type')
                    ->required(),
                TextInput::make('name')
                    ->required(),
                Textarea::make('description')
                    ->columnSpanFull(),
                DatePicker::make('start_date'),
                DatePicker::make('end_date'),
                Textarea::make('days_of_week')
                    ->columnSpanFull(),
                TextInput::make('adjustment_type')
                    ->required()
                    ->default('percentage'),
                TextInput::make('adjustment_value')
                    ->required()
                    ->numeric(),
                TextInput::make('min_nights')
                    ->numeric(),
                TextInput::make('max_nights')
                    ->numeric(),
                TextInput::make('advance_booking_days')
                    ->numeric(),
                TextInput::make('last_minute_days')
                    ->numeric(),
                TextInput::make('priority')
                    ->required()
                    ->numeric()
                    ->default(0),
                Toggle::make('is_active')
                    ->required(),
            ]);
    }
}
