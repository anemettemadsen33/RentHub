<?php

namespace App\Filament\Resources\ConciergeServices\Tables;

use App\Enums\ConciergeServiceType;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class ConciergeServicesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('images')
                    ->label('Image')
                    ->circular()
                    ->stacked()
                    ->limit(1)
                    ->defaultImageUrl(url('/images/placeholder-service.jpg')),
                
                TextColumn::make('name')
                    ->label('Service Name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                
                TextColumn::make('service_type')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(fn ($state) => ConciergeServiceType::from($state)->label())
                    ->icon(fn ($state) => ConciergeServiceType::from($state)->icon())
                    ->searchable()
                    ->sortable(),
                
                TextColumn::make('serviceProvider.name')
                    ->label('Provider')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                
                TextColumn::make('base_price')
                    ->label('Price')
                    ->money('USD')
                    ->sortable()
                    ->suffix(fn ($record) => ' / ' . $record->price_unit),
                
                TextColumn::make('duration_minutes')
                    ->label('Duration')
                    ->formatStateUsing(function ($state) {
                        if (!$state) return 'Variable';
                        $hours = floor($state / 60);
                        $minutes = $state % 60;
                        if ($hours > 0) {
                            return $hours . 'h' . ($minutes > 0 ? ' ' . $minutes . 'm' : '');
                        }
                        return $minutes . 'm';
                    })
                    ->sortable()
                    ->toggleable(),
                
                IconColumn::make('is_available')
                    ->label('Available')
                    ->boolean()
                    ->sortable(),
                
                TextColumn::make('bookings_count')
                    ->label('Bookings')
                    ->counts('bookings')
                    ->badge()
                    ->color('success')
                    ->sortable(),
                
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('service_type')
                    ->label('Service Type')
                    ->options(ConciergeServiceType::class)
                    ->multiple(),
                
                TernaryFilter::make('is_available')
                    ->label('Availability')
                    ->placeholder('All services')
                    ->trueLabel('Available only')
                    ->falseLabel('Unavailable only'),
                
                SelectFilter::make('service_provider')
                    ->relationship('serviceProvider', 'name')
                    ->searchable()
                    ->preload(),
                
                TrashedFilter::make(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
