<?php

namespace App\Filament\Resources\Invoices\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class InvoiceInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('invoice_number'),
                TextEntry::make('booking.id')
                    ->numeric(),
                TextEntry::make('user.name')
                    ->numeric(),
                TextEntry::make('property.title')
                    ->numeric(),
                TextEntry::make('bankAccount.id')
                    ->numeric(),
                TextEntry::make('invoice_date')
                    ->date(),
                TextEntry::make('due_date')
                    ->date(),
                TextEntry::make('status'),
                TextEntry::make('subtotal')
                    ->numeric(),
                TextEntry::make('cleaning_fee')
                    ->numeric(),
                TextEntry::make('security_deposit')
                    ->numeric(),
                TextEntry::make('taxes')
                    ->numeric(),
                TextEntry::make('total_amount')
                    ->numeric(),
                TextEntry::make('currency'),
                TextEntry::make('customer_name'),
                TextEntry::make('customer_email'),
                TextEntry::make('customer_phone'),
                TextEntry::make('property_title'),
                TextEntry::make('paid_at')
                    ->dateTime(),
                TextEntry::make('payment_method'),
                TextEntry::make('payment_reference'),
                TextEntry::make('pdf_path'),
                TextEntry::make('sent_at')
                    ->dateTime(),
                TextEntry::make('send_count')
                    ->numeric(),
                TextEntry::make('created_at')
                    ->dateTime(),
                TextEntry::make('updated_at')
                    ->dateTime(),
            ]);
    }
}
