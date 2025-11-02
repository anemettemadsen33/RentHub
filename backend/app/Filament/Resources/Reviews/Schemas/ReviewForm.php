<?php

namespace App\Filament\Resources\Reviews\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ReviewForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('property_id')
                    ->relationship('property', 'title')
                    ->required(),
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required(),
                Select::make('booking_id')
                    ->relationship('booking', 'id'),
                TextInput::make('rating')
                    ->required()
                    ->numeric(),
                Textarea::make('comment')
                    ->columnSpanFull(),
                TextInput::make('cleanliness_rating')
                    ->numeric(),
                TextInput::make('communication_rating')
                    ->numeric(),
                TextInput::make('check_in_rating')
                    ->numeric(),
                TextInput::make('accuracy_rating')
                    ->numeric(),
                TextInput::make('location_rating')
                    ->numeric(),
                TextInput::make('value_rating')
                    ->numeric(),
                Toggle::make('is_approved')
                    ->required(),
                Textarea::make('admin_notes')
                    ->columnSpanFull(),
                Textarea::make('owner_response')
                    ->columnSpanFull(),
                DateTimePicker::make('owner_response_at'),
            ]);
    }
}
