<?php

namespace App\Filament\Resources\GuestVerificationResource\Pages;

use App\Filament\Resources\GuestVerificationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListGuestVerifications extends ListRecords
{
    protected static string $resource = GuestVerificationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
