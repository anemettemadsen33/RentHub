<?php

namespace App\Filament\Resources\InsurancePlans\Pages;

use App\Filament\Resources\InsurancePlans\InsurancePlanResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListInsurancePlans extends ListRecords
{
    protected static string $resource = InsurancePlanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
