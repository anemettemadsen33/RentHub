<?php

namespace App\Filament\Resources\InsurancePlans\Pages;

use App\Filament\Resources\InsurancePlans\InsurancePlanResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditInsurancePlan extends EditRecord
{
    protected static string $resource = InsurancePlanResource::class;

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
