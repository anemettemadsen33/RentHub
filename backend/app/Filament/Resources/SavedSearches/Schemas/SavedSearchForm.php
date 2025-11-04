<?php

namespace App\Filament\Resources\SavedSearches\Schemas;

use Filament\Forms;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Schema;

class SavedSearchForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            self::getSearchDetailsSection(),
            self::getLocationSection(),
            self::getPriceSection(),
            self::getPropertyFiltersSection(),
            self::getDateSection(),
            self::getAlertSection(),
            self::getStatisticsSection(),
        ]);
    }

    private static function getSearchDetailsSection(): Component
    {
        return Forms\Components\Section::make('Search Details')
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required()
                    ->searchable(),
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Toggle::make('is_active')
                    ->default(true),
            ])
            ->columns(3);
    }

    private static function getLocationSection(): Component
    {
        return Forms\Components\Section::make('Location Filters')
            ->schema([
                Forms\Components\TextInput::make('location')
                    ->maxLength(255),
                Forms\Components\TextInput::make('latitude')
                    ->numeric(),
                Forms\Components\TextInput::make('longitude')
                    ->numeric(),
                Forms\Components\TextInput::make('radius_km')
                    ->numeric()
                    ->suffix('km')
                    ->minValue(1)
                    ->maxValue(100),
            ])
            ->columns(4);
    }

    private static function getPriceSection(): Component
    {
        return Forms\Components\Section::make('Price Range')
            ->schema([
                Forms\Components\TextInput::make('min_price')
                    ->numeric()
                    ->prefix('€')
                    ->minValue(0),
                Forms\Components\TextInput::make('max_price')
                    ->numeric()
                    ->prefix('€')
                    ->minValue(0),
            ])
            ->columns(2);
    }

    private static function getPropertyFiltersSection(): Component
    {
        return Forms\Components\Section::make('Property Filters')
            ->schema([
                Forms\Components\TextInput::make('min_bedrooms')
                    ->numeric()
                    ->minValue(0),
                Forms\Components\TextInput::make('max_bedrooms')
                    ->numeric()
                    ->minValue(0),
                Forms\Components\TextInput::make('min_bathrooms')
                    ->numeric()
                    ->minValue(0),
                Forms\Components\TextInput::make('max_bathrooms')
                    ->numeric()
                    ->minValue(0),
                Forms\Components\TextInput::make('min_guests')
                    ->numeric()
                    ->minValue(1),
                Forms\Components\TextInput::make('property_type')
                    ->maxLength(50),
            ])
            ->columns(3);
    }

    private static function getDateSection(): Component
    {
        return Forms\Components\Section::make('Date Range')
            ->schema([
                Forms\Components\DatePicker::make('check_in'),
                Forms\Components\DatePicker::make('check_out'),
            ])
            ->columns(2);
    }

    private static function getAlertSection(): Component
    {
        return Forms\Components\Section::make('Alert Settings')
            ->schema([
                Forms\Components\Toggle::make('enable_alerts')
                    ->default(true),
                Forms\Components\Select::make('alert_frequency')
                    ->options([
                        'instant' => 'Instant',
                        'daily' => 'Daily',
                        'weekly' => 'Weekly',
                    ])
                    ->default('daily'),
                Forms\Components\DateTimePicker::make('last_alert_sent_at')
                    ->disabled(),
                Forms\Components\TextInput::make('new_listings_count')
                    ->numeric()
                    ->disabled(),
            ])
            ->columns(2);
    }

    private static function getStatisticsSection(): Component
    {
        return Forms\Components\Section::make('Statistics')
            ->schema([
                Forms\Components\TextInput::make('search_count')
                    ->numeric()
                    ->disabled(),
                Forms\Components\DateTimePicker::make('last_searched_at')
                    ->disabled(),
            ])
            ->columns(2);
    }
}
