<?php

namespace App\Filament\Resources\ExternalCalendars\Pages;

use App\Filament\Resources\ExternalCalendars\ExternalCalendarResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListExternalCalendars extends ListRecords
{
    protected static string $resource = ExternalCalendarResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
