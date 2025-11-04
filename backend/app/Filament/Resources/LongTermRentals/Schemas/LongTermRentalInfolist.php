<?php

namespace App\Filament\Resources\LongTermRentals\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class LongTermRentalInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('property.title')
                    ->numeric(),
                TextEntry::make('tenant.name')
                    ->numeric(),
                TextEntry::make('owner.name')
                    ->numeric(),
                TextEntry::make('start_date')
                    ->date(),
                TextEntry::make('end_date')
                    ->date(),
                TextEntry::make('duration_months')
                    ->numeric(),
                TextEntry::make('rental_type'),
                TextEntry::make('monthly_rent')
                    ->numeric(),
                TextEntry::make('security_deposit')
                    ->numeric(),
                TextEntry::make('total_rent')
                    ->numeric(),
                TextEntry::make('payment_frequency'),
                TextEntry::make('payment_day_of_month')
                    ->numeric(),
                TextEntry::make('deposit_status'),
                TextEntry::make('deposit_paid_amount')
                    ->numeric(),
                TextEntry::make('deposit_paid_at')
                    ->dateTime(),
                TextEntry::make('deposit_returned_amount')
                    ->numeric(),
                TextEntry::make('deposit_returned_at')
                    ->dateTime(),
                TextEntry::make('lease_agreement_path'),
                TextEntry::make('lease_signed_at')
                    ->dateTime(),
                IconEntry::make('lease_auto_generated')
                    ->boolean(),
                TextEntry::make('utilities_estimate')
                    ->numeric(),
                IconEntry::make('maintenance_included')
                    ->boolean(),
                TextEntry::make('status'),
                TextEntry::make('cancelled_at')
                    ->dateTime(),
                IconEntry::make('auto_renewable')
                    ->boolean(),
                TextEntry::make('renewal_notice_days')
                    ->numeric(),
                TextEntry::make('renewal_requested_at')
                    ->dateTime(),
                TextEntry::make('renewal_status'),
                IconEntry::make('pets_allowed')
                    ->boolean(),
                IconEntry::make('smoking_allowed')
                    ->boolean(),
                TextEntry::make('move_in_inspection_at')
                    ->dateTime(),
                TextEntry::make('move_out_inspection_at')
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
