<?php

namespace App\Filament\Resources\ExternalCalendars\Pages;

use App\Filament\Resources\ExternalCalendars\ExternalCalendarResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditExternalCalendar extends EditRecord
{
    protected static string $resource = ExternalCalendarResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
