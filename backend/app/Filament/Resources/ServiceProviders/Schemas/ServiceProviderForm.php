<?php

namespace App\Filament\Resources\ServiceProviders\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ServiceProviderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('company_name'),
                TextInput::make('type')
                    ->required()
                    ->default('cleaning'),
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->required(),
                TextInput::make('phone')
                    ->tel()
                    ->required(),
                TextInput::make('secondary_phone')
                    ->tel(),
                Textarea::make('address')
                    ->required()
                    ->columnSpanFull(),
                TextInput::make('city')
                    ->required(),
                TextInput::make('state'),
                TextInput::make('zip_code')
                    ->required(),
                TextInput::make('business_license'),
                TextInput::make('insurance_policy'),
                DatePicker::make('insurance_expiry'),
                Textarea::make('certifications')
                    ->columnSpanFull(),
                Textarea::make('service_areas')
                    ->columnSpanFull(),
                Textarea::make('services_offered')
                    ->columnSpanFull(),
                Textarea::make('maintenance_specialties')
                    ->columnSpanFull(),
                TextInput::make('hourly_rate')
                    ->numeric(),
                TextInput::make('base_rate')
                    ->numeric(),
                TextInput::make('pricing_type')
                    ->required()
                    ->default('per_service'),
                Textarea::make('working_hours')
                    ->columnSpanFull(),
                Textarea::make('holidays')
                    ->columnSpanFull(),
                Toggle::make('emergency_available')
                    ->required(),
                TextInput::make('average_rating')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('total_jobs')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('completed_jobs')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('cancelled_jobs')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('response_time_hours')
                    ->numeric(),
                TextInput::make('status')
                    ->required()
                    ->default('pending_verification'),
                Toggle::make('verified')
                    ->required(),
                DateTimePicker::make('verified_at'),
                Textarea::make('documents')
                    ->columnSpanFull(),
                Textarea::make('photos')
                    ->columnSpanFull(),
                Textarea::make('bio')
                    ->columnSpanFull(),
                Textarea::make('notes')
                    ->columnSpanFull(),
            ]);
    }
}
