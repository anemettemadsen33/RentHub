<?php

namespace App\Filament\Resources\LongTermRentals\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class LongTermRentalForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('property_id')
                    ->relationship('property', 'title')
                    ->required(),
                Select::make('tenant_id')
                    ->relationship('tenant', 'name')
                    ->required(),
                Select::make('owner_id')
                    ->relationship('owner', 'name')
                    ->required(),
                DatePicker::make('start_date')
                    ->required(),
                DatePicker::make('end_date')
                    ->required(),
                TextInput::make('duration_months')
                    ->required()
                    ->numeric(),
                TextInput::make('rental_type')
                    ->required()
                    ->default('monthly'),
                TextInput::make('monthly_rent')
                    ->required()
                    ->numeric(),
                TextInput::make('security_deposit')
                    ->required()
                    ->numeric(),
                TextInput::make('total_rent')
                    ->required()
                    ->numeric(),
                TextInput::make('payment_frequency')
                    ->required()
                    ->default('monthly'),
                TextInput::make('payment_day_of_month')
                    ->required()
                    ->numeric()
                    ->default(1),
                TextInput::make('deposit_status')
                    ->required()
                    ->default('pending'),
                TextInput::make('deposit_paid_amount')
                    ->numeric(),
                DateTimePicker::make('deposit_paid_at'),
                TextInput::make('deposit_returned_amount')
                    ->numeric(),
                DateTimePicker::make('deposit_returned_at'),
                TextInput::make('lease_agreement_path'),
                DateTimePicker::make('lease_signed_at'),
                Toggle::make('lease_auto_generated')
                    ->required(),
                Textarea::make('utilities_included')
                    ->columnSpanFull(),
                Textarea::make('utilities_paid_by_tenant')
                    ->columnSpanFull(),
                TextInput::make('utilities_estimate')
                    ->numeric(),
                Toggle::make('maintenance_included')
                    ->required(),
                Textarea::make('maintenance_terms')
                    ->columnSpanFull(),
                TextInput::make('status')
                    ->required()
                    ->default('draft'),
                Textarea::make('cancellation_reason')
                    ->columnSpanFull(),
                DateTimePicker::make('cancelled_at'),
                Toggle::make('auto_renewable')
                    ->required(),
                TextInput::make('renewal_notice_days')
                    ->required()
                    ->numeric()
                    ->default(30),
                DateTimePicker::make('renewal_requested_at'),
                TextInput::make('renewal_status')
                    ->required()
                    ->default('not_requested'),
                Textarea::make('special_terms')
                    ->columnSpanFull(),
                Textarea::make('house_rules')
                    ->columnSpanFull(),
                Toggle::make('pets_allowed')
                    ->required(),
                Toggle::make('smoking_allowed')
                    ->required(),
                DateTimePicker::make('move_in_inspection_at'),
                DateTimePicker::make('move_out_inspection_at'),
                Textarea::make('move_in_condition_notes')
                    ->columnSpanFull(),
                Textarea::make('move_out_condition_notes')
                    ->columnSpanFull(),
            ]);
    }
}
