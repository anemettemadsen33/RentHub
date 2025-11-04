<?php

namespace App\Filament\Resources\SmartLocks\Pages;

use App\Filament\Resources\SmartLocks\SmartLockResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewSmartLock extends ViewRecord
{
    protected static string $resource = SmartLockResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
