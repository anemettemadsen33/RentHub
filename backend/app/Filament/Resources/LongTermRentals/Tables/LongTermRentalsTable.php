<?php

namespace App\Filament\Resources\LongTermRentals\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class LongTermRentalsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('property.title')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('tenant.name')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('owner.name')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('start_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('end_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('duration_months')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('rental_type')
                    ->searchable(),
                TextColumn::make('monthly_rent')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('security_deposit')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('total_rent')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('payment_frequency')
                    ->searchable(),
                TextColumn::make('payment_day_of_month')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('deposit_status')
                    ->searchable(),
                TextColumn::make('deposit_paid_amount')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('deposit_paid_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('deposit_returned_amount')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('deposit_returned_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('lease_agreement_path')
                    ->searchable(),
                TextColumn::make('lease_signed_at')
                    ->dateTime()
                    ->sortable(),
                IconColumn::make('lease_auto_generated')
                    ->boolean(),
                TextColumn::make('utilities_estimate')
                    ->numeric()
                    ->sortable(),
                IconColumn::make('maintenance_included')
                    ->boolean(),
                TextColumn::make('status')
                    ->searchable(),
                TextColumn::make('cancelled_at')
                    ->dateTime()
                    ->sortable(),
                IconColumn::make('auto_renewable')
                    ->boolean(),
                TextColumn::make('renewal_notice_days')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('renewal_requested_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('renewal_status')
                    ->searchable(),
                IconColumn::make('pets_allowed')
                    ->boolean(),
                IconColumn::make('smoking_allowed')
                    ->boolean(),
                TextColumn::make('move_in_inspection_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('move_out_inspection_at')
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
                TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
