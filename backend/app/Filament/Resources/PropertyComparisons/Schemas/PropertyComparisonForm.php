<?php

namespace App\Filament\Resources\PropertyComparisons\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class PropertyComparisonForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required(),
                Textarea::make('property_ids')
                    ->required()
                    ->columnSpanFull(),
                TextInput::make('session_id'),
                DateTimePicker::make('expires_at'),
            ]);
    }
}
