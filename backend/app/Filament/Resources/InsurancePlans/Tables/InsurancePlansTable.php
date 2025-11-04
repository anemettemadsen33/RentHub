<?php

namespace App\Filament\Resources\InsurancePlans\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class InsurancePlansTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                
                \Filament\Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->colors([
                        'primary' => 'cancellation',
                        'success' => 'damage',
                        'warning' => 'liability',
                        'info' => 'travel',
                        'danger' => 'comprehensive',
                    ])
                    ->sortable(),
                
                \Filament\Tables\Columns\TextColumn::make('max_coverage')
                    ->money('EUR')
                    ->label('Coverage')
                    ->sortable(),
                
                \Filament\Tables\Columns\TextColumn::make('fixed_price')
                    ->money('EUR')
                    ->label('Fixed Price')
                    ->sortable()
                    ->toggleable(),
                
                \Filament\Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable(),
                
                \Filament\Tables\Columns\IconColumn::make('is_mandatory')
                    ->label('Mandatory')
                    ->boolean()
                    ->sortable(),
                
                \Filament\Tables\Columns\TextColumn::make('bookingInsurances_count')
                    ->counts('bookingInsurances')
                    ->label('Active Policies')
                    ->sortable(),
                
                \Filament\Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
                \Filament\Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'cancellation' => 'Cancellation',
                        'damage' => 'Damage',
                        'liability' => 'Liability',
                        'travel' => 'Travel',
                        'comprehensive' => 'Comprehensive',
                    ]),
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
            ])
            ->defaultSort('display_order');
    }
}
