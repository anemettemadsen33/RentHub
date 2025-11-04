<?php

namespace App\Filament\Resources\Payments\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class PaymentInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('payment_number'),
                TextEntry::make('booking.id')
                    ->numeric(),
                TextEntry::make('invoice.id')
                    ->numeric(),
                TextEntry::make('user.name')
                    ->numeric(),
                TextEntry::make('amount')
                    ->numeric(),
                TextEntry::make('currency'),
                TextEntry::make('type'),
                TextEntry::make('status'),
                TextEntry::make('payment_method'),
                TextEntry::make('payment_gateway'),
                TextEntry::make('transaction_id'),
                TextEntry::make('gateway_reference'),
                TextEntry::make('bank_reference'),
                TextEntry::make('initiated_at')
                    ->dateTime(),
                TextEntry::make('completed_at')
                    ->dateTime(),
                TextEntry::make('failed_at')
                    ->dateTime(),
                TextEntry::make('refunded_at')
                    ->dateTime(),
                TextEntry::make('created_at')
                    ->dateTime(),
                TextEntry::make('updated_at')
                    ->dateTime(),
            ]);
    }
}
