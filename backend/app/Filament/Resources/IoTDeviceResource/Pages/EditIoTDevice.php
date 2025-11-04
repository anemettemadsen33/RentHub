<?php

namespace App\Filament\Resources\IoTDeviceResource\Pages;

use App\Filament\Resources\IoTDeviceResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditIoTDevice extends EditRecord
{
    protected static string $resource = IoTDeviceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
