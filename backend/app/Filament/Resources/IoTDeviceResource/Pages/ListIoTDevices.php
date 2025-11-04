<?php

namespace App\Filament\Resources\IoTDeviceResource\Pages;

use App\Filament\Resources\IoTDeviceResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListIoTDevices extends ListRecords
{
    protected static string $resource = IoTDeviceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
