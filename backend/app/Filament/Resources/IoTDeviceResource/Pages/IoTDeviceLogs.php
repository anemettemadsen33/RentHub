<?php

namespace App\Filament\Resources\IoTDeviceResource\Pages;

use App\Filament\Resources\IoTDeviceResource;
use App\Models\IoTDevice;
use Filament\Resources\Pages\Page;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;

class IoTDeviceLogs extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string $resource = IoTDeviceResource::class;

    public function getView(): string
    {
        return 'filament.resources.iot-device-resource.pages.iot-device-logs';
    }

    public IoTDevice $record;

    public function table(Table $table): Table
    {
        return $table
            ->query($this->record->logs()->getQuery())
            ->columns([
                Tables\Columns\TextColumn::make('event_timestamp')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('event_type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'state_change' => 'success',
                        'error' => 'danger',
                        'maintenance' => 'warning',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('description')
                    ->wrap(),
                Tables\Columns\TextColumn::make('event_data')
                    ->formatStateUsing(fn ($state) => json_encode($state, JSON_PRETTY_PRINT))
                    ->wrap(),
            ])
            ->defaultSort('event_timestamp', 'desc');
    }
}
