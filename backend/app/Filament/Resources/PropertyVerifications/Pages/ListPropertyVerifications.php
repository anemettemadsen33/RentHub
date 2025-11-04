<?php

namespace App\Filament\Resources\PropertyVerifications\Pages;

use App\Filament\Resources\PropertyVerifications\PropertyVerificationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPropertyVerifications extends ListRecords
{
    protected static string $resource = PropertyVerificationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
