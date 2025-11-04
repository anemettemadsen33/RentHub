<?php

namespace App\Filament\Resources\CleaningSchedules\Pages;

use App\Filament\Resources\CleaningSchedules\CleaningScheduleResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewCleaningSchedule extends ViewRecord
{
    protected static string $resource = CleaningScheduleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
