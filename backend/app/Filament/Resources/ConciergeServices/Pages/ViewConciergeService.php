<?php

namespace App\Filament\Resources\ConciergeServices\Pages;

use App\Filament\Resources\ConciergeServices\ConciergeServiceResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewConciergeService extends ViewRecord
{
    protected static string $resource = ConciergeServiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
