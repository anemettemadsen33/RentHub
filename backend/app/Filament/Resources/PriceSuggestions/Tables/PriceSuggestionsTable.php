<?php

namespace App\Filament\Resources\PriceSuggestions\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PriceSuggestionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('property.title')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('start_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('end_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('current_price')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('suggested_price')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('min_recommended_price')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('max_recommended_price')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('confidence_score')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('market_average_price')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('competitor_count')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('occupancy_rate')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('demand_score')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('historical_price')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('historical_occupancy')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('status')
                    ->searchable(),
                TextColumn::make('accepted_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('rejected_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('expires_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('model_version')
                    ->searchable(),
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
