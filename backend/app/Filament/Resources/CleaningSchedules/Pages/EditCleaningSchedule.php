<?php

namespace App\Filament\Resources\CleaningSchedules\Pages;

use App\Filament\Resources\CleaningSchedules\CleaningScheduleResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditCleaningSchedule extends EditRecord
{
    protected static string $resource = CleaningScheduleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
