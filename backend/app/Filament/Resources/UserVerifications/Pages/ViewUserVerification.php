<?php

namespace App\Filament\Resources\UserVerifications\Pages;

use App\Filament\Resources\UserVerifications\UserVerificationResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewUserVerification extends ViewRecord
{
    protected static string $resource = UserVerificationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
