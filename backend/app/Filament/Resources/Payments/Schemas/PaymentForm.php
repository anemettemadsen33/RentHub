<?php

namespace App\Filament\Resources\Payments\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class PaymentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('payment_number')
                    ->required(),
                Select::make('booking_id')
                    ->relationship('booking', 'id')
                    ->required(),
                Select::make('invoice_id')
                    ->relationship('invoice', 'id'),
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required(),
                TextInput::make('amount')
                    ->required()
                    ->numeric(),
                TextInput::make('currency')
                    ->required()
                    ->default('EUR'),
                TextInput::make('type')
                    ->required()
                    ->default('full'),
                TextInput::make('status')
                    ->required()
                    ->default('pending'),
                TextInput::make('payment_method')
                    ->required(),
                TextInput::make('payment_gateway'),
                TextInput::make('transaction_id'),
                TextInput::make('gateway_reference'),
                TextInput::make('bank_reference'),
                Textarea::make('bank_receipt')
                    ->columnSpanFull(),
                DateTimePicker::make('initiated_at'),
                DateTimePicker::make('completed_at'),
                DateTimePicker::make('failed_at'),
                DateTimePicker::make('refunded_at'),
                Textarea::make('failure_reason')
                    ->columnSpanFull(),
                Textarea::make('notes')
                    ->columnSpanFull(),
                Textarea::make('metadata')
                    ->columnSpanFull(),
            ]);
    }
}
