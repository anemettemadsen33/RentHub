<?php

namespace App\Filament\Resources\Reviews\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ReviewForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Review Information')
                    ->schema([
                        Grid::make(2)->schema([
                            Select::make('property_id')
                                ->label('Property')
                                ->relationship('property', 'title')
                                ->searchable()
                                ->preload()
                                ->required(),
                            Select::make('user_id')
                                ->label('Reviewer')
                                ->relationship('user', 'name')
                                ->searchable()
                                ->preload()
                                ->required(),
                        ]),
                        Select::make('booking_id')
                            ->label('Related Booking')
                            ->relationship('booking', 'id')
                            ->searchable()
                            ->preload(),
                    ]),

                Section::make('Rating')
                    ->schema([
                        Grid::make(3)->schema([
                            TextInput::make('rating')
                                ->label('Overall Rating (1-5)')
                                ->required()
                                ->numeric()
                                ->minValue(1)
                                ->maxValue(5)
                                ->step(1),
                            TextInput::make('helpful_count')
                                ->label('Helpful Votes')
                                ->numeric()
                                ->default(0)
                                ->disabled(),
                        ]),
                        Textarea::make('comment')
                            ->label('Review Comment')
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),

                Section::make('Detailed Ratings')
                    ->schema([
                        Grid::make(3)->schema([
                            TextInput::make('cleanliness_rating')
                                ->label('Cleanliness (1-5)')
                                ->numeric()
                                ->minValue(1)
                                ->maxValue(5),
                            TextInput::make('communication_rating')
                                ->label('Communication (1-5)')
                                ->numeric()
                                ->minValue(1)
                                ->maxValue(5),
                            TextInput::make('check_in_rating')
                                ->label('Check-in (1-5)')
                                ->numeric()
                                ->minValue(1)
                                ->maxValue(5),
                            TextInput::make('accuracy_rating')
                                ->label('Accuracy (1-5)')
                                ->numeric()
                                ->minValue(1)
                                ->maxValue(5),
                            TextInput::make('location_rating')
                                ->label('Location (1-5)')
                                ->numeric()
                                ->minValue(1)
                                ->maxValue(5),
                            TextInput::make('value_rating')
                                ->label('Value (1-5)')
                                ->numeric()
                                ->minValue(1)
                                ->maxValue(5),
                        ]),
                    ])
                    ->collapsible(),

                Section::make('Photos')
                    ->schema([
                        FileUpload::make('photos')
                            ->label('Review Photos')
                            ->image()
                            ->multiple()
                            ->maxFiles(5)
                            ->maxSize(5120)
                            ->directory('reviews')
                            ->columnSpanFull()
                            ->helperText('Upload up to 5 photos (max 5MB each)'),
                    ])
                    ->collapsible(),

                Section::make('Moderation')
                    ->schema([
                        Toggle::make('is_approved')
                            ->label('Approved')
                            ->default(true)
                            ->helperText('Toggle to approve or hide this review'),
                        Textarea::make('admin_notes')
                            ->label('Admin Notes (Internal)')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),

                Section::make('Owner Response')
                    ->schema([
                        Textarea::make('owner_response')
                            ->label('Response from Property Owner')
                            ->rows(3)
                            ->columnSpanFull(),
                        DateTimePicker::make('owner_response_at')
                            ->label('Response Date')
                            ->disabled(),
                    ])
                    ->collapsible(),
            ]);
    }
}
