<?php

namespace App\Filament\Resources\UserVerifications\Pages;

use App\Filament\Resources\UserVerifications\UserVerificationResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditUserVerification extends EditRecord
{
    protected static string $resource = UserVerificationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
