<?php

namespace App\Filament\Resources\GuestVerificationResource\Pages;

use App\Filament\Resources\GuestVerificationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditGuestVerification extends EditRecord
{
    protected static string $resource = GuestVerificationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Update trust score when saving
        $this->record->updateTrustScore();

        return $data;
    }
}
