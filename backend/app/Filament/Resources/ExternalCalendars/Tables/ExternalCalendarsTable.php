<?php

namespace App\Filament\Resources\ExternalCalendars\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\Action;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Http;

class ExternalCalendarsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('property.title')
                    ->searchable()
                    ->sortable()
                    ->url(fn ($record) => route('filament.admin.resources.properties.properties.edit', $record->property)),

                TextColumn::make('platform')
                    ->badge()
                    ->colors([
                        'danger' => 'airbnb',
                        'info' => 'booking',
                        'warning' => 'vrbo',
                        'success' => 'google',
                        'gray' => 'ical',
                    ])
                    ->formatStateUsing(fn (string $state): string => ucfirst($state))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                IconColumn::make('sync_enabled')
                    ->label('Auto Sync')
                    ->boolean()
                    ->sortable(),

                TextColumn::make('last_synced_at')
                    ->label('Last Synced')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('Never')
                    ->since(),

                TextColumn::make('sync_error')
                    ->label('Status')
                    ->badge()
                    ->color(fn (?string $state): string => $state ? 'danger' : 'success')
                    ->formatStateUsing(fn (?string $state): string => $state ? 'Error' : 'OK')
                    ->tooltip(fn (?string $state): ?string => $state)
                    ->toggleable(),

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
                SelectFilter::make('platform')
                    ->options([
                        'airbnb' => 'Airbnb',
                        'booking' => 'Booking.com',
                        'vrbo' => 'VRBO',
                        'google' => 'Google Calendar',
                        'ical' => 'iCal',
                    ]),

                SelectFilter::make('property')
                    ->relationship('property', 'title')
                    ->searchable()
                    ->preload(),

                TernaryFilter::make('sync_enabled')
                    ->label('Auto Sync')
                    ->placeholder('All')
                    ->trueLabel('Enabled')
                    ->falseLabel('Disabled'),

                TernaryFilter::make('sync_error')
                    ->label('Has Errors')
                    ->placeholder('All')
                    ->trueLabel('With Errors')
                    ->falseLabel('No Errors')
                    ->queries(
                        true: fn ($query) => $query->whereNotNull('sync_error'),
                        false: fn ($query) => $query->whereNull('sync_error'),
                    ),
            ])
            ->recordActions([
                Action::make('sync')
                    ->label('Sync')
                    ->icon('heroicon-o-arrow-path')
                    ->color('info')
                    ->action(function ($record) {
                        try {
                            $propertyId = $record->property_id;
                            $response = Http::withToken(auth()->user()->createToken('api-sync')->plainTextToken)
                                ->post(url("/api/v1/properties/{$propertyId}/external-calendars/{$record->id}/sync"));

                            if ($response->successful()) {
                                $data = $response->json();
                                Notification::make()
                                    ->title('Calendar synced successfully')
                                    ->body("Added {$data['dates_added']} dates, removed {$data['dates_removed']} dates")
                                    ->success()
                                    ->send();
                            } else {
                                throw new \Exception($response->json('message') ?? 'Sync failed');
                            }
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Sync failed')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
