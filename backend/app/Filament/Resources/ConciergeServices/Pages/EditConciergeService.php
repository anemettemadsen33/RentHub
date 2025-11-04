<?php

namespace App\Filament\Resources\ConciergeServices\Pages;

use App\Filament\Resources\ConciergeServices\ConciergeServiceResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditConciergeService extends EditRecord
{
    protected static string $resource = ConciergeServiceResource::class;

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
