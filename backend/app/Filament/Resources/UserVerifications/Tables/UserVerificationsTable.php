<?php

namespace App\Filament\Resources\UserVerifications\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UserVerificationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('id_verification_status')
                    ->searchable(),
                TextColumn::make('id_document_type')
                    ->searchable(),
                TextColumn::make('id_document_number')
                    ->searchable(),
                ImageColumn::make('id_front_image'),
                ImageColumn::make('id_back_image'),
                ImageColumn::make('selfie_image'),
                TextColumn::make('id_verified_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('phone_verification_status')
                    ->searchable(),
                TextColumn::make('phone_number')
                    ->searchable(),
                TextColumn::make('phone_verification_code')
                    ->searchable(),
                TextColumn::make('phone_verified_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('phone_verification_code_sent_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('email_verification_status')
                    ->searchable(),
                TextColumn::make('email_verified_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('address_verification_status')
                    ->searchable(),
                TextColumn::make('address_proof_document')
                    ->searchable(),
                ImageColumn::make('address_proof_image'),
                TextColumn::make('address_verified_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('background_check_status')
                    ->searchable(),
                TextColumn::make('background_check_provider')
                    ->searchable(),
                TextColumn::make('background_check_reference')
                    ->searchable(),
                TextColumn::make('background_check_completed_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('overall_status')
                    ->searchable(),
                TextColumn::make('verification_score')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('reviewed_by')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
