<?php

namespace App\Filament\Resources\CleaningSchedules\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class CleaningSchedulesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('property.title')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('serviceProvider.name')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('created_by')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('schedule_type')
                    ->searchable(),
                TextColumn::make('frequency')
                    ->searchable(),
                TextColumn::make('day_of_month')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('preferred_time')
                    ->time()
                    ->sortable(),
                TextColumn::make('duration_hours')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('service_type')
                    ->searchable(),
                TextColumn::make('start_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('end_date')
                    ->date()
                    ->sortable(),
                IconColumn::make('active')
                    ->boolean(),
                TextColumn::make('last_executed_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('next_execution_at')
                    ->dateTime()
                    ->sortable(),
                IconColumn::make('auto_book')
                    ->boolean(),
                TextColumn::make('book_days_in_advance')
                    ->numeric()
                    ->sortable(),
                IconColumn::make('notify_provider')
                    ->boolean(),
                IconColumn::make('notify_owner')
                    ->boolean(),
                TextColumn::make('reminder_hours_before')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
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
            ]);
    }
}
