<?php

namespace App\Filament\Resources;

use App\Filament\Resources\IoTDeviceResource\Pages;
use App\Models\IoTDevice;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class IoTDeviceResource extends Resource
{
    protected static ?string $model = IoTDevice::class;

    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-o-cpu-chip';
    }

    public static function getNavigationLabel(): string
    {
        return 'IoT Devices';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Property Management';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\Section::make('Device Information')
                    ->schema([
                        Forms\Components\Select::make('property_id')
                            ->relationship('property', 'title')
                            ->searchable()
                            ->required(),
                        Forms\Components\Select::make('iot_device_type_id')
                            ->relationship('deviceType', 'name')
                            ->required(),
                        Forms\Components\TextInput::make('device_name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('device_id')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        Forms\Components\TextInput::make('manufacturer')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('model')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('location_in_property')
                            ->required()
                            ->placeholder('e.g., Living Room, Master Bedroom')
                            ->maxLength(255),
                    ])->columns(2),

                Forms\Components\Section::make('Device Settings')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->options([
                                'online' => 'Online',
                                'offline' => 'Offline',
                                'maintenance' => 'Maintenance',
                            ])
                            ->default('offline')
                            ->required(),
                        Forms\Components\Toggle::make('guest_accessible')
                            ->label('Guest Can Control')
                            ->helperText('Allow guests to control this device during their stay'),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Active')
                            ->default(true),
                    ])->columns(3),

                Forms\Components\Section::make('Configuration')
                    ->schema([
                        Forms\Components\KeyValue::make('current_state')
                            ->label('Current State')
                            ->helperText('Current device state (JSON)'),
                        Forms\Components\KeyValue::make('configuration')
                            ->label('Device Configuration')
                            ->helperText('Device-specific settings (JSON)'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('property.title')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('deviceType.name')
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('device_name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('location_in_property')
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'online' => 'success',
                        'offline' => 'danger',
                        'maintenance' => 'warning',
                    }),
                Tables\Columns\IconColumn::make('guest_accessible')
                    ->boolean(),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
                Tables\Columns\TextColumn::make('last_communication')
                    ->dateTime()
                    ->sortable()
                    ->since(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'online' => 'Online',
                        'offline' => 'Offline',
                        'maintenance' => 'Maintenance',
                    ]),
                Tables\Filters\SelectFilter::make('iot_device_type_id')
                    ->relationship('deviceType', 'name')
                    ->label('Device Type'),
                Tables\Filters\TernaryFilter::make('guest_accessible'),
                Tables\Filters\TernaryFilter::make('is_active'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('view_logs')
                    ->icon('heroicon-o-document-text')
                    ->url(fn (IoTDevice $record): string => route('filament.admin.resources.io-t-devices.logs', $record)),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListIoTDevices::route('/'),
            'create' => Pages\CreateIoTDevice::route('/create'),
            'edit' => Pages\EditIoTDevice::route('/{record}/edit'),
            'logs' => Pages\IoTDeviceLogs::route('/{record}/logs'),
        ];
    }
}
