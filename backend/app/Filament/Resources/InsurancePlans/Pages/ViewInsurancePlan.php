<?php

namespace App\Filament\Resources\InsurancePlans\Pages;

use App\Filament\Resources\InsurancePlans\InsurancePlanResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewInsurancePlan extends ViewRecord
{
    protected static string $resource = InsurancePlanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
