<?php

namespace App\Filament\Resources\Bookings\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class BookingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('property_id')
                    ->relationship('property', 'title')
                    ->required(),
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required(),
                DatePicker::make('check_in')
                    ->required(),
                DatePicker::make('check_out')
                    ->required(),
                TextInput::make('guests')
                    ->required()
                    ->numeric(),
                TextInput::make('nights')
                    ->required()
                    ->numeric(),
                TextInput::make('price_per_night')
                    ->required()
                    ->numeric(),
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
                TextInput::make('status')
                    ->required()
                    ->default('pending'),
                TextInput::make('guest_name')
                    ->required(),
                TextInput::make('guest_email')
                    ->email()
                    ->required(),
                TextInput::make('guest_phone')
                    ->tel(),
                Textarea::make('special_requests')
                    ->columnSpanFull(),
                TextInput::make('payment_status')
                    ->required()
                    ->default('pending'),
                TextInput::make('payment_method'),
                TextInput::make('payment_transaction_id'),
                DateTimePicker::make('paid_at'),
                DateTimePicker::make('confirmed_at'),
                DateTimePicker::make('cancelled_at'),
            ]);
    }
}
