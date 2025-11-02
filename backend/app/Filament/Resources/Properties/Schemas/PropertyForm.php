<?php

namespace App\Filament\Resources\Properties\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class PropertyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->required(),
                Textarea::make('description')
                    ->required()
                    ->columnSpanFull(),
                TextInput::make('type')
                    ->required(),
                TextInput::make('bedrooms')
                    ->required()
                    ->numeric()
                    ->default(1),
                TextInput::make('bathrooms')
                    ->required()
                    ->numeric()
                    ->default(1),
                TextInput::make('guests')
                    ->required()
                    ->numeric()
                    ->default(1),
                TextInput::make('price_per_night')
                    ->required()
                    ->numeric(),
                TextInput::make('cleaning_fee')
                    ->numeric(),
                TextInput::make('security_deposit')
                    ->numeric(),
                TextInput::make('street_address')
                    ->required(),
                TextInput::make('city')
                    ->required(),
                TextInput::make('state')
                    ->required(),
                TextInput::make('country')
                    ->required(),
                TextInput::make('postal_code')
                    ->required(),
                TextInput::make('latitude')
                    ->numeric(),
                TextInput::make('longitude')
                    ->numeric(),
                TextInput::make('area_sqm')
                    ->numeric(),
                TextInput::make('built_year')
                    ->numeric(),
                Toggle::make('is_active')
                    ->required(),
                Toggle::make('is_featured')
                    ->required(),
                DateTimePicker::make('available_from'),
                DateTimePicker::make('available_until'),
                Textarea::make('images')
                    ->columnSpanFull(),
                FileUpload::make('main_image')
                    ->image(),
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required(),
            ]);
    }
}
