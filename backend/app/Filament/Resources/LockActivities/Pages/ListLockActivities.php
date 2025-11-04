<?php

namespace App\Filament\Resources\LockActivities\Pages;

use App\Filament\Resources\LockActivities\LockActivityResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListLockActivities extends ListRecords
{
    protected static string $resource = LockActivityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
