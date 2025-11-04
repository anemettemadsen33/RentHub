<?php

namespace App\Filament\Resources\ExternalCalendars\Pages;

use App\Filament\Resources\ExternalCalendars\ExternalCalendarResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewExternalCalendar extends ViewRecord
{
    protected static string $resource = ExternalCalendarResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
