<?php

namespace App\Filament\Resources\Properties\RelationManagers;

use BackedEnum;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Http;

class ExternalCalendarsRelationManager extends RelationManager
{
    protected static string $relationship = 'externalCalendars';

    protected static ?string $title = 'External Calendars';

    protected static string|BackedEnum|null $icon = Heroicon::OutlinedCalendar;

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\Select::make('platform')
                    ->label('Platform')
                    ->options([
                        'airbnb' => 'Airbnb',
                        'booking' => 'Booking.com',
                        'vrbo' => 'VRBO',
                        'google' => 'Google Calendar',
                        'ical' => 'iCal (Generic)',
                    ])
                    ->required()
                    ->native(false),

                Forms\Components\TextInput::make('name')
                    ->label('Calendar Name')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('e.g., My Airbnb Listing'),

                Forms\Components\TextInput::make('url')
                    ->label('iCal URL')
                    ->required()
                    ->url()
                    ->maxLength(500)
                    ->placeholder('https://...')
                    ->helperText('Paste the iCal export URL from your external platform')
                    ->columnSpanFull(),

                Forms\Components\Toggle::make('sync_enabled')
                    ->label('Enable Automatic Sync')
                    ->default(true)
                    ->helperText('Automatically sync this calendar every 6 hours'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('platform')
                    ->badge()
                    ->colors([
                        'danger' => 'airbnb',
                        'info' => 'booking',
                        'warning' => 'vrbo',
                        'success' => 'google',
                        'gray' => 'ical',
                    ])
                    ->formatStateUsing(fn (string $state): string => ucfirst($state)),

                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\IconColumn::make('sync_enabled')
                    ->label('Auto Sync')
                    ->boolean(),

                Tables\Columns\TextColumn::make('last_synced_at')
                    ->label('Last Synced')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('Never')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('sync_error')
                    ->label('Status')
                    ->badge()
                    ->color(fn (?string $state): string => $state ? 'danger' : 'success')
                    ->formatStateUsing(fn (?string $state): string => $state ? 'Error' : 'OK')
                    ->tooltip(fn (?string $state): ?string => $state)
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('platform')
                    ->options([
                        'airbnb' => 'Airbnb',
                        'booking' => 'Booking.com',
                        'vrbo' => 'VRBO',
                        'google' => 'Google Calendar',
                        'ical' => 'iCal',
                    ]),

                Tables\Filters\TernaryFilter::make('sync_enabled')
                    ->label('Auto Sync Enabled')
                    ->placeholder('All')
                    ->trueLabel('Enabled')
                    ->falseLabel('Disabled'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->icon('heroicon-o-plus'),
            ])
            ->actions([
                Tables\Actions\Action::make('sync')
                    ->label('Sync Now')
                    ->icon('heroicon-o-arrow-path')
                    ->color('info')
                    ->action(function ($record) {
                        try {
                            $propertyId = $this->getOwnerRecord()->id;
                            $response = Http::withToken(auth()->user()->createToken('api-sync')->plainTextToken)
                                ->post(url("/api/v1/properties/{$propertyId}/external-calendars/{$record->id}/sync"));

                            if ($response->successful()) {
                                $data = $response->json();
                                Notification::make()
                                    ->title('Calendar synced successfully')
                                    ->body("Added {$data['dates_added']} dates, removed {$data['dates_removed']} dates")
                                    ->success()
                                    ->send();

                                $this->refreshTable();
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
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Sync calendar now?')
                    ->modalDescription('This will fetch the latest availability from the external calendar.')
                    ->modalSubmitActionLabel('Yes, sync now'),

                Tables\Actions\Action::make('view_logs')
                    ->label('View Logs')
                    ->icon('heroicon-o-document-text')
                    ->color('gray')
                    ->url(fn ($record) => route('filament.admin.resources.external-calendars.external-calendars.view', $record))
                    ->openUrlInNewTab(),

                Tables\Actions\EditAction::make(),

                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
                    ->icon('heroicon-o-plus'),
            ])
            ->emptyStateHeading('No external calendars')
            ->emptyStateDescription('Connect external calendars from Airbnb, Booking.com, or other platforms to sync availability automatically.')
            ->emptyStateIcon('heroicon-o-calendar');
    }
}
