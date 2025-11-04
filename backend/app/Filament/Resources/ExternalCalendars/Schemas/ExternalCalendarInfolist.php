<?php

namespace App\Filament\Resources\ExternalCalendars\Schemas;

use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ExternalCalendarInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Calendar Information')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('property.title')
                                    ->label('Property')
                                    ->url(fn ($record) => route('filament.admin.resources.properties.properties.edit', $record->property)),

                                TextEntry::make('platform')
                                    ->badge()
                                    ->colors([
                                        'danger' => 'airbnb',
                                        'info' => 'booking',
                                        'warning' => 'vrbo',
                                        'success' => 'google',
                                        'gray' => 'ical',
                                    ])
                                    ->formatStateUsing(fn (string $state): string => ucfirst($state)),

                                TextEntry::make('name')
                                    ->label('Calendar Name'),

                                IconEntry::make('sync_enabled')
                                    ->label('Auto Sync Enabled')
                                    ->boolean(),
                            ]),

                        TextEntry::make('url')
                            ->label('iCal URL')
                            ->copyable()
                            ->columnSpanFull(),
                    ]),

                Section::make('Sync Status')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('last_synced_at')
                                    ->label('Last Synced')
                                    ->dateTime()
                                    ->since()
                                    ->placeholder('Never'),

                                TextEntry::make('sync_error')
                                    ->label('Sync Status')
                                    ->badge()
                                    ->color(fn (?string $state): string => $state ? 'danger' : 'success')
                                    ->formatStateUsing(fn (?string $state): string => $state ? 'Error' : 'OK'),
                            ]),

                        TextEntry::make('sync_error')
                            ->label('Error Message')
                            ->color('danger')
                            ->columnSpanFull()
                            ->visible(fn ($record) => ! empty($record->sync_error)),
                    ]),

                Section::make('Timestamps')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('created_at')
                                    ->dateTime(),

                                TextEntry::make('updated_at')
                                    ->dateTime(),
                            ]),
                    ])
                    ->collapsible()
                    ->collapsed(),
            ]);
    }
}
