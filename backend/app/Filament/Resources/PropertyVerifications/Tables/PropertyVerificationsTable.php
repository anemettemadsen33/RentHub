<?php

namespace App\Filament\Resources\PropertyVerifications\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PropertyVerificationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('property.title')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('user.name')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('ownership_status')
                    ->searchable(),
                TextColumn::make('ownership_document_type')
                    ->searchable(),
                TextColumn::make('ownership_verified_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('inspection_status')
                    ->searchable(),
                TextColumn::make('inspection_scheduled_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('inspection_completed_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('inspector.name')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('inspection_score')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('photos_status')
                    ->searchable(),
                TextColumn::make('photos_verified_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('details_status')
                    ->searchable(),
                TextColumn::make('details_verified_at')
                    ->dateTime()
                    ->sortable(),
                IconColumn::make('has_business_license')
                    ->boolean(),
                TextColumn::make('business_license_document')
                    ->searchable(),
                IconColumn::make('has_safety_certificate')
                    ->boolean(),
                TextColumn::make('safety_certificate_document')
                    ->searchable(),
                IconColumn::make('has_insurance')
                    ->boolean(),
                TextColumn::make('insurance_document')
                    ->searchable(),
                TextColumn::make('insurance_expiry_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('overall_status')
                    ->searchable(),
                IconColumn::make('has_verified_badge')
                    ->boolean(),
                TextColumn::make('verification_score')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('reviewed_by')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('reviewed_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('next_verification_due')
                    ->date()
                    ->sortable(),
                TextColumn::make('last_verified_at')
                    ->dateTime()
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
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
