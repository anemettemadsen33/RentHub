<?php

namespace App\Filament\Resources\CleaningSchedules\Pages;

use App\Filament\Resources\CleaningSchedules\CleaningScheduleResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCleaningSchedules extends ListRecords
{
    protected static string $resource = CleaningScheduleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
