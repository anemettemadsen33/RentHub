<?php

namespace App\Filament\Resources\Payouts\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PayoutForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('payout_number')
                    ->required(),
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required(),
                Select::make('booking_id')
                    ->relationship('booking', 'id'),
                Select::make('bank_account_id')
                    ->relationship('bankAccount', 'id'),
                TextInput::make('booking_amount')
                    ->required()
                    ->numeric(),
                TextInput::make('commission_rate')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('commission_amount')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('payout_amount')
                    ->required()
                    ->numeric(),
                TextInput::make('currency')
                    ->required()
                    ->default('EUR'),
                TextInput::make('status')
                    ->required()
                    ->default('pending'),
                DatePicker::make('payout_date')
                    ->required(),
                DateTimePicker::make('completed_at'),
                DateTimePicker::make('failed_at'),
                TextInput::make('payment_method'),
                TextInput::make('transaction_reference'),
                DatePicker::make('period_start'),
                DatePicker::make('period_end'),
                Textarea::make('failure_reason')
                    ->columnSpanFull(),
                Textarea::make('notes')
                    ->columnSpanFull(),
                Textarea::make('metadata')
                    ->columnSpanFull(),
            ]);
    }
}
