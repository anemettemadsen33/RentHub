<?php

namespace App\Filament\Resources\Invoices\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class InvoiceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('invoice_number')
                    ->required(),
                Select::make('booking_id')
                    ->relationship('booking', 'id')
                    ->required(),
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required(),
                Select::make('property_id')
                    ->relationship('property', 'title')
                    ->required(),
                Select::make('bank_account_id')
                    ->relationship('bankAccount', 'id'),
                DatePicker::make('invoice_date')
                    ->required(),
                DatePicker::make('due_date')
                    ->required(),
                TextInput::make('status')
                    ->required()
                    ->default('draft'),
                TextInput::make('subtotal')
                    ->required()
                    ->numeric(),
                TextInput::make('cleaning_fee')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('security_deposit')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('taxes')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('total_amount')
                    ->required()
                    ->numeric(),
                TextInput::make('currency')
                    ->required()
                    ->default('EUR'),
                TextInput::make('customer_name')
                    ->required(),
                TextInput::make('customer_email')
                    ->email()
                    ->required(),
                TextInput::make('customer_phone')
                    ->tel(),
                Textarea::make('customer_address')
                    ->columnSpanFull(),
                TextInput::make('property_title')
                    ->required(),
                Textarea::make('property_address')
                    ->columnSpanFull(),
                DateTimePicker::make('paid_at'),
                TextInput::make('payment_method'),
                TextInput::make('payment_reference'),
                TextInput::make('pdf_path'),
                DateTimePicker::make('sent_at'),
                TextInput::make('send_count')
                    ->required()
                    ->numeric()
                    ->default(0),
                Textarea::make('notes')
                    ->columnSpanFull(),
            ]);
    }
}
