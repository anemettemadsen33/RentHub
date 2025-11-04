<?php

namespace App\Filament\Resources\PropertyVerifications\Pages;

use App\Filament\Resources\PropertyVerifications\PropertyVerificationResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewPropertyVerification extends ViewRecord
{
    protected static string $resource = PropertyVerificationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
