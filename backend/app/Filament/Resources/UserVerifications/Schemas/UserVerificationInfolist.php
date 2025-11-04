<?php

namespace App\Filament\Resources\UserVerifications\Schemas;

use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class UserVerificationInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('user.name')
                    ->numeric(),
                TextEntry::make('id_verification_status'),
                TextEntry::make('id_document_type'),
                TextEntry::make('id_document_number'),
                ImageEntry::make('id_front_image'),
                ImageEntry::make('id_back_image'),
                ImageEntry::make('selfie_image'),
                TextEntry::make('id_verified_at')
                    ->dateTime(),
                TextEntry::make('phone_verification_status'),
                TextEntry::make('phone_number'),
                TextEntry::make('phone_verification_code'),
                TextEntry::make('phone_verified_at')
                    ->dateTime(),
                TextEntry::make('phone_verification_code_sent_at')
                    ->dateTime(),
                TextEntry::make('email_verification_status'),
                TextEntry::make('email_verified_at')
                    ->dateTime(),
                TextEntry::make('address_verification_status'),
                TextEntry::make('address_proof_document'),
                ImageEntry::make('address_proof_image'),
                TextEntry::make('address_verified_at')
                    ->dateTime(),
                TextEntry::make('background_check_status'),
                TextEntry::make('background_check_provider'),
                TextEntry::make('background_check_reference'),
                TextEntry::make('background_check_completed_at')
                    ->dateTime(),
                TextEntry::make('overall_status'),
                TextEntry::make('verification_score')
                    ->numeric(),
                TextEntry::make('reviewed_by')
                    ->numeric(),
                TextEntry::make('created_at')
                    ->dateTime(),
                TextEntry::make('updated_at')
                    ->dateTime(),
            ]);
    }
}
