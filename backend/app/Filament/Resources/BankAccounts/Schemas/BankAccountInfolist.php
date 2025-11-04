<?php

namespace App\Filament\Resources\BankAccounts\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class BankAccountInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('user.name')
                    ->numeric(),
                TextEntry::make('account_name'),
                TextEntry::make('account_holder_name'),
                TextEntry::make('iban'),
                TextEntry::make('bic_swift'),
                TextEntry::make('bank_name'),
                TextEntry::make('bank_address'),
                TextEntry::make('currency'),
                IconEntry::make('is_default')
                    ->boolean(),
                IconEntry::make('is_active')
                    ->boolean(),
                TextEntry::make('account_type'),
                TextEntry::make('created_at')
                    ->dateTime(),
                TextEntry::make('updated_at')
                    ->dateTime(),
            ]);
    }
}
