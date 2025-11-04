<?php

namespace App\Filament\Resources\ServiceProviders\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ServiceProviderInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name'),
                TextEntry::make('company_name'),
                TextEntry::make('type'),
                TextEntry::make('email')
                    ->label('Email address'),
                TextEntry::make('phone'),
                TextEntry::make('secondary_phone'),
                TextEntry::make('city'),
                TextEntry::make('state'),
                TextEntry::make('zip_code'),
                TextEntry::make('business_license'),
                TextEntry::make('insurance_policy'),
                TextEntry::make('insurance_expiry')
                    ->date(),
                TextEntry::make('hourly_rate')
                    ->numeric(),
                TextEntry::make('base_rate')
                    ->numeric(),
                TextEntry::make('pricing_type'),
                IconEntry::make('emergency_available')
                    ->boolean(),
                TextEntry::make('average_rating')
                    ->numeric(),
                TextEntry::make('total_jobs')
                    ->numeric(),
                TextEntry::make('completed_jobs')
                    ->numeric(),
                TextEntry::make('cancelled_jobs')
                    ->numeric(),
                TextEntry::make('response_time_hours')
                    ->numeric(),
                TextEntry::make('status'),
                IconEntry::make('verified')
                    ->boolean(),
                TextEntry::make('verified_at')
                    ->dateTime(),
                TextEntry::make('created_at')
                    ->dateTime(),
                TextEntry::make('updated_at')
                    ->dateTime(),
                TextEntry::make('deleted_at')
                    ->dateTime(),
            ]);
    }
}
