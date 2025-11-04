<?php

namespace App\Filament\Resources\Properties\Schemas;

use App\Models\Amenity;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class PropertyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Basic Information')
                    ->schema([
                        TextInput::make('title')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Textarea::make('description')
                            ->required()
                            ->rows(4)
                            ->columnSpanFull(),
                        Select::make('type')
                            ->required()
                            ->options([
                                'apartment' => 'Apartment',
                                'house' => 'House',
                                'villa' => 'Villa',
                                'studio' => 'Studio',
                                'condo' => 'Condo',
                                'townhouse' => 'Townhouse',
                                'loft' => 'Loft',
                                'other' => 'Other',
                            ]),
                        Select::make('furnishing_status')
                            ->label('Furnishing')
                            ->options([
                                'furnished' => 'Furnished',
                                'semi_furnished' => 'Semi-Furnished',
                                'unfurnished' => 'Unfurnished',
                            ])
                            ->default('unfurnished')
                            ->required(),
                    ])
                    ->columns(2),

                Section::make('Property Details')
                    ->schema([
                        TextInput::make('bedrooms')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->default(1)
                            ->suffix('bedrooms'),
                        TextInput::make('bathrooms')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->default(1)
                            ->suffix('bathrooms'),
                        TextInput::make('guests')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->default(1)
                            ->suffix('guests'),
                        TextInput::make('area_sqm')
                            ->label('Area (m²)')
                            ->numeric()
                            ->minValue(0)
                            ->suffix('m²'),
                        TextInput::make('square_footage')
                            ->label('Square Footage')
                            ->numeric()
                            ->minValue(0)
                            ->suffix('sq ft')
                            ->helperText('Optional: Area in square feet'),
                        TextInput::make('built_year')
                            ->label('Year Built')
                            ->numeric()
                            ->minValue(1800)
                            ->maxValue(date('Y'))
                            ->length(4),
                        TextInput::make('floor_number')
                            ->label('Floor Number')
                            ->numeric()
                            ->minValue(0)
                            ->helperText('Ground floor = 0, 1st floor = 1, etc.'),
                        Toggle::make('parking_available')
                            ->label('Parking Available')
                            ->live()
                            ->default(false),
                        TextInput::make('parking_spaces')
                            ->label('Number of Parking Spaces')
                            ->numeric()
                            ->minValue(1)
                            ->visible(fn ($get) => $get('parking_available')),
                    ])
                    ->columns(3),

                Section::make('Pricing')
                    ->schema([
                        TextInput::make('price_per_night')
                            ->label('Price per Night')
                            ->required()
                            ->numeric()
                            ->prefix('$')
                            ->minValue(0),
                        TextInput::make('cleaning_fee')
                            ->label('Cleaning Fee')
                            ->numeric()
                            ->prefix('$')
                            ->minValue(0),
                        TextInput::make('security_deposit')
                            ->label('Security Deposit')
                            ->numeric()
                            ->prefix('$')
                            ->minValue(0),
                    ])
                    ->columns(3),

                Section::make('Location')
                    ->schema([
                        TextInput::make('street_address')
                            ->label('Street Address')
                            ->required()
                            ->columnSpanFull(),
                        TextInput::make('city')
                            ->required(),
                        TextInput::make('state')
                            ->required(),
                        TextInput::make('country')
                            ->required(),
                        TextInput::make('postal_code')
                            ->label('Postal Code')
                            ->required(),
                        TextInput::make('latitude')
                            ->numeric()
                            ->helperText('Auto-filled from map'),
                        TextInput::make('longitude')
                            ->numeric()
                            ->helperText('Auto-filled from map'),
                    ])
                    ->columns(3),

                Section::make('Status & Availability')
                    ->schema([
                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true),
                        Toggle::make('is_featured')
                            ->label('Featured'),
                        DateTimePicker::make('available_from')
                            ->label('Available From'),
                        DateTimePicker::make('available_until')
                            ->label('Available Until'),
                    ])
                    ->columns(2),

                Section::make('Amenities')
                    ->description('Select all amenities available at this property')
                    ->schema(
                        collect(['basic', 'comfort', 'outdoor', 'luxury', 'transportation', 'safety', 'accessibility', 'family'])
                            ->map(function ($category) {
                                return CheckboxList::make('amenities_'.$category)
                                    ->label(ucfirst($category).' Amenities')
                                    ->relationship('amenities', 'name', fn ($query) => $query->where('category', $category))
                                    ->options(function () use ($category) {
                                        return Amenity::query()
                                            ->byCategory($category)
                                            ->ordered()
                                            ->pluck('name', 'id')
                                            ->toArray();
                                    })
                                    ->columns(3)
                                    ->gridDirection('row')
                                    ->bulkToggleable()
                                    ->columnSpanFull();
                            })
                            ->toArray()
                    )
                    ->collapsed()
                    ->columns(1),

                Section::make('Images')
                    ->schema([
                        FileUpload::make('main_image')
                            ->label('Main Image')
                            ->image()
                            ->directory('properties/main')
                            ->columnSpanFull(),
                        Textarea::make('images')
                            ->label('Additional Images (JSON)')
                            ->helperText('For now, enter JSON array. Will be improved later.')
                            ->columnSpanFull(),
                    ]),

                Select::make('user_id')
                    ->label('Owner')
                    ->relationship('user', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),
            ])
            ->columns(1);
    }
}
