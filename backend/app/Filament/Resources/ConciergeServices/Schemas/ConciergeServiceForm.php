<?php

namespace App\Filament\Resources\ConciergeServices\Schemas;

use App\Enums\ConciergeServiceType;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ConciergeServiceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Basic Information')
                    ->schema([
                        Select::make('service_provider_id')
                            ->label('Service Provider')
                            ->relationship('serviceProvider', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        
                        Select::make('service_type')
                            ->label('Service Type')
                            ->options(ConciergeServiceType::class)
                            ->required()
                            ->native(false),
                        
                        TextInput::make('name')
                            ->label('Service Name')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('e.g., Luxury Airport Transfer'),
                        
                        Textarea::make('description')
                            ->label('Description')
                            ->required()
                            ->rows(4)
                            ->columnSpanFull()
                            ->placeholder('Detailed description of the service...'),
                        
                        Toggle::make('is_available')
                            ->label('Available')
                            ->default(true)
                            ->inline(false),
                    ])
                    ->columns(2),
                
                Section::make('Pricing')
                    ->schema([
                        TextInput::make('base_price')
                            ->label('Base Price')
                            ->required()
                            ->numeric()
                            ->prefix('$')
                            ->minValue(0)
                            ->step(0.01),
                        
                        Select::make('price_unit')
                            ->label('Price Unit')
                            ->options([
                                'per service' => 'Per Service',
                                'per hour' => 'Per Hour',
                                'per person' => 'Per Person',
                                'per day' => 'Per Day',
                            ])
                            ->default('per service')
                            ->required()
                            ->native(false),
                        
                        KeyValue::make('pricing_extras')
                            ->label('Extra Charges')
                            ->keyLabel('Item')
                            ->valueLabel('Price ($)')
                            ->addActionLabel('Add extra charge')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                
                Section::make('Service Details')
                    ->schema([
                        TextInput::make('duration_minutes')
                            ->label('Duration (minutes)')
                            ->numeric()
                            ->minValue(0)
                            ->suffix('minutes')
                            ->placeholder('Leave empty if variable'),
                        
                        TextInput::make('max_guests')
                            ->label('Maximum Guests')
                            ->numeric()
                            ->minValue(1)
                            ->placeholder('Leave empty if unlimited'),
                        
                        TextInput::make('advance_booking_hours')
                            ->label('Advance Booking Required (hours)')
                            ->required()
                            ->numeric()
                            ->default(24)
                            ->minValue(1)
                            ->suffix('hours'),
                        
                        Repeater::make('requirements')
                            ->label('Requirements')
                            ->simple(
                                TextInput::make('requirement')
                                    ->placeholder('e.g., Valid passport required')
                            )
                            ->addActionLabel('Add requirement')
                            ->columnSpanFull(),
                    ])
                    ->columns(3),
                
                Section::make('Images')
                    ->schema([
                        FileUpload::make('images')
                            ->label('Service Images')
                            ->image()
                            ->multiple()
                            ->maxFiles(5)
                            ->directory('concierge-services')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
