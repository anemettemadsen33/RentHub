<?php

namespace App\Filament\Resources\InsurancePlans\Schemas;

use Filament\Schemas\Components\Checkbox;
use Filament\Schemas\Components\KeyValue;
use Filament\Schemas\Components\Repeater;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Select;
use Filament\Schemas\Components\Textarea;
use Filament\Schemas\Components\TextInput;
use Filament\Schemas\Schema;

class InsurancePlanForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Basic Information')
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        TextInput::make('slug')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->helperText('URL-friendly identifier'),

                        Select::make('type')
                            ->required()
                            ->options([
                                'cancellation' => 'Cancellation Insurance',
                                'damage' => 'Damage Protection',
                                'liability' => 'Liability Coverage',
                                'travel' => 'Travel Insurance',
                                'comprehensive' => 'Comprehensive Coverage',
                            ]),

                        Textarea::make('description')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Pricing Configuration')
                    ->schema([
                        TextInput::make('fixed_price')
                            ->numeric()
                            ->prefix('€')
                            ->default(0)
                            ->helperText('Fixed price per booking'),

                        TextInput::make('price_per_night')
                            ->numeric()
                            ->prefix('€')
                            ->default(0)
                            ->helperText('Price per night'),

                        TextInput::make('price_percentage')
                            ->numeric()
                            ->suffix('%')
                            ->default(0)
                            ->minValue(0)
                            ->maxValue(100)
                            ->helperText('Percentage of booking total'),

                        TextInput::make('max_coverage')
                            ->required()
                            ->numeric()
                            ->prefix('€')
                            ->helperText('Maximum coverage amount'),
                    ])
                    ->columns(2),

                Section::make('Eligibility Criteria')
                    ->schema([
                        TextInput::make('min_nights')
                            ->required()
                            ->numeric()
                            ->default(1)
                            ->minValue(1),

                        TextInput::make('max_nights')
                            ->numeric()
                            ->nullable()
                            ->helperText('Leave empty for no limit'),

                        TextInput::make('min_booking_value')
                            ->numeric()
                            ->prefix('€')
                            ->default(0),

                        TextInput::make('max_booking_value')
                            ->numeric()
                            ->prefix('€')
                            ->nullable()
                            ->helperText('Leave empty for no limit'),
                    ])
                    ->columns(2),

                Section::make('Coverage Details')
                    ->schema([
                        KeyValue::make('coverage_details')
                            ->keyLabel('Coverage Item')
                            ->valueLabel('Description')
                            ->addActionLabel('Add Coverage Item')
                            ->columnSpanFull(),

                        Repeater::make('exclusions')
                            ->simple(
                                TextInput::make('exclusion')
                                    ->label('Exclusion')
                            )
                            ->addActionLabel('Add Exclusion')
                            ->columnSpanFull(),
                    ]),

                Section::make('Terms & Status')
                    ->schema([
                        Textarea::make('terms_and_conditions')
                            ->rows(5)
                            ->columnSpanFull(),

                        Checkbox::make('is_active')
                            ->default(true)
                            ->label('Active'),

                        Checkbox::make('is_mandatory')
                            ->default(false)
                            ->label('Mandatory for all bookings'),

                        TextInput::make('display_order')
                            ->numeric()
                            ->default(0)
                            ->helperText('Order in which plans are displayed'),
                    ])
                    ->columns(2),
            ]);
    }
}
