<?php

namespace App\Filament\Resources\PropertyVerifications\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class PropertyVerificationInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('property.title')
                    ->numeric(),
                TextEntry::make('user.name')
                    ->numeric(),
                TextEntry::make('ownership_status'),
                TextEntry::make('ownership_document_type'),
                TextEntry::make('ownership_verified_at')
                    ->dateTime(),
                TextEntry::make('inspection_status'),
                TextEntry::make('inspection_scheduled_at')
                    ->dateTime(),
                TextEntry::make('inspection_completed_at')
                    ->dateTime(),
                TextEntry::make('inspector.name')
                    ->numeric(),
                TextEntry::make('inspection_score')
                    ->numeric(),
                TextEntry::make('photos_status'),
                TextEntry::make('photos_verified_at')
                    ->dateTime(),
                TextEntry::make('details_status'),
                TextEntry::make('details_verified_at')
                    ->dateTime(),
                IconEntry::make('has_business_license')
                    ->boolean(),
                TextEntry::make('business_license_document'),
                IconEntry::make('has_safety_certificate')
                    ->boolean(),
                TextEntry::make('safety_certificate_document'),
                IconEntry::make('has_insurance')
                    ->boolean(),
                TextEntry::make('insurance_document'),
                TextEntry::make('insurance_expiry_date')
                    ->date(),
                TextEntry::make('overall_status'),
                IconEntry::make('has_verified_badge')
                    ->boolean(),
                TextEntry::make('verification_score')
                    ->numeric(),
                TextEntry::make('reviewed_by')
                    ->numeric(),
                TextEntry::make('reviewed_at')
                    ->dateTime(),
                TextEntry::make('next_verification_due')
                    ->date(),
                TextEntry::make('last_verified_at')
                    ->dateTime(),
                TextEntry::make('created_at')
                    ->dateTime(),
                TextEntry::make('updated_at')
                    ->dateTime(),
            ]);
    }
}
