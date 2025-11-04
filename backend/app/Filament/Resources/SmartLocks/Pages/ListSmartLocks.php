<?php

namespace App\Filament\Resources\SmartLocks\Pages;

use App\Filament\Resources\SmartLocks\SmartLockResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSmartLocks extends ListRecords
{
    protected static string $resource = SmartLockResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
