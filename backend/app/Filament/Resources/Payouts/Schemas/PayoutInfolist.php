<?php

namespace App\Filament\Resources\Payouts\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class PayoutInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('payout_number'),
                TextEntry::make('user.name')
                    ->numeric(),
                TextEntry::make('booking.id')
                    ->numeric(),
                TextEntry::make('bankAccount.id')
                    ->numeric(),
                TextEntry::make('booking_amount')
                    ->numeric(),
                TextEntry::make('commission_rate')
                    ->numeric(),
                TextEntry::make('commission_amount')
                    ->numeric(),
                TextEntry::make('payout_amount')
                    ->numeric(),
                TextEntry::make('currency'),
                TextEntry::make('status'),
                TextEntry::make('payout_date')
                    ->date(),
                TextEntry::make('completed_at')
                    ->dateTime(),
                TextEntry::make('failed_at')
                    ->dateTime(),
                TextEntry::make('payment_method'),
                TextEntry::make('transaction_reference'),
                TextEntry::make('period_start')
                    ->date(),
                TextEntry::make('period_end')
                    ->date(),
                TextEntry::make('created_at')
                    ->dateTime(),
                TextEntry::make('updated_at')
                    ->dateTime(),
            ]);
    }
}
