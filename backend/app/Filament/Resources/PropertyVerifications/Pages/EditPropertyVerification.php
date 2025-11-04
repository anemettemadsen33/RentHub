<?php

namespace App\Filament\Resources\PropertyVerifications\Pages;

use App\Filament\Resources\PropertyVerifications\PropertyVerificationResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditPropertyVerification extends EditRecord
{
    protected static string $resource = PropertyVerificationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
