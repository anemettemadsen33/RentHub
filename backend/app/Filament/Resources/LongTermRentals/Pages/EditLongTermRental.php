<?php

namespace App\Filament\Resources\LongTermRentals\Pages;

use App\Filament\Resources\LongTermRentals\LongTermRentalResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditLongTermRental extends EditRecord
{
    protected static string $resource = LongTermRentalResource::class;

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
