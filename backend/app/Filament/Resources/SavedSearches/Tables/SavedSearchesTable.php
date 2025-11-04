<?php

namespace App\Filament\Resources\SavedSearches\Tables;

use App\Models\SavedSearch;
use Filament\Tables;
use Filament\Tables\Table;

class SavedSearchesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('location')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('min_price')
                    ->money('EUR')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('max_price')
                    ->money('EUR')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\IconColumn::make('enable_alerts')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('alert_frequency')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'instant' => 'danger',
                        'daily' => 'warning',
                        'weekly' => 'success',
                        default => 'gray',
                    })
                    ->toggleable(),
                Tables\Columns\TextColumn::make('search_count')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('new_listings_count')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('last_searched_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active'),
                Tables\Filters\TernaryFilter::make('enable_alerts'),
                Tables\Filters\SelectFilter::make('alert_frequency')
                    ->options([
                        'instant' => 'Instant',
                        'daily' => 'Daily',
                        'weekly' => 'Weekly',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('execute')
                    ->icon('heroicon-o-play')
                    ->color('success')
                    ->action(function (SavedSearch $record) {
                        $results = $record->executeSearch();
                        \Filament\Notifications\Notification::make()
                            ->title('Search executed')
                            ->body("Found {$results->count()} matching properties")
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
