<?php

namespace App\Filament\Resources\Reviews\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ReviewsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('property.title')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('user.name')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('booking.id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('rating')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('cleanliness_rating')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('communication_rating')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('check_in_rating')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('accuracy_rating')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('location_rating')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('value_rating')
                    ->numeric()
                    ->sortable(),
                IconColumn::make('is_approved')
                    ->boolean(),
                TextColumn::make('owner_response_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
