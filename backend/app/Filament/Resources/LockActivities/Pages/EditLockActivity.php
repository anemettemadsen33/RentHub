<?php

namespace App\Filament\Resources\LockActivities\Pages;

use App\Filament\Resources\LockActivities\LockActivityResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditLockActivity extends EditRecord
{
    protected static string $resource = LockActivityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
