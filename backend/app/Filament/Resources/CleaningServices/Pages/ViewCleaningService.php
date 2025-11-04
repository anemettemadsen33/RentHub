<?php

namespace App\Filament\Resources\CleaningServices\Pages;

use App\Filament\Resources\CleaningServices\CleaningServiceResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewCleaningService extends ViewRecord
{
    protected static string $resource = CleaningServiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
