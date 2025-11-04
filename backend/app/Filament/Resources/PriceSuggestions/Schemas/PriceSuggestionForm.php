<?php

namespace App\Filament\Resources\PriceSuggestions\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PriceSuggestionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('property_id')
                    ->relationship('property', 'title')
                    ->required(),
                DatePicker::make('start_date')
                    ->required(),
                DatePicker::make('end_date')
                    ->required(),
                TextInput::make('current_price')
                    ->required()
                    ->numeric(),
                TextInput::make('suggested_price')
                    ->required()
                    ->numeric(),
                TextInput::make('min_recommended_price')
                    ->numeric(),
                TextInput::make('max_recommended_price')
                    ->numeric(),
                TextInput::make('confidence_score')
                    ->required()
                    ->numeric()
                    ->default(0),
                Textarea::make('factors')
                    ->columnSpanFull(),
                TextInput::make('market_average_price')
                    ->numeric(),
                TextInput::make('competitor_count')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('occupancy_rate')
                    ->numeric(),
                TextInput::make('demand_score')
                    ->numeric(),
                TextInput::make('historical_price')
                    ->numeric(),
                TextInput::make('historical_occupancy')
                    ->numeric(),
                TextInput::make('status')
                    ->required()
                    ->default('pending'),
                DateTimePicker::make('accepted_at'),
                DateTimePicker::make('rejected_at'),
                DateTimePicker::make('expires_at'),
                TextInput::make('model_version'),
                Textarea::make('notes')
                    ->columnSpanFull(),
            ]);
    }
}
