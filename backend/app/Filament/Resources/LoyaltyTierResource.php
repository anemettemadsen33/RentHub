<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LoyaltyTierResource\Pages;
use App\Models\LoyaltyTier;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class LoyaltyTierResource extends Resource
{
    protected static ?string $model = LoyaltyTier::class;

    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-o-star';
    }

    public static function getNavigationLabel(): string
    {
        return 'Loyalty Tiers';
    }

    public static function getNavigationSort(): ?int
    {
        return 1;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\Section::make('Tier Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        Forms\Components\TextInput::make('order')
                            ->numeric()
                            ->default(0),
                        Forms\Components\ColorPicker::make('badge_color')
                            ->default('#6B7280'),
                        Forms\Components\TextInput::make('icon')
                            ->maxLength(255)
                            ->placeholder('heroicon-o-star'),
                        Forms\Components\Toggle::make('is_active')
                            ->default(true),
                    ])->columns(2),

                Forms\Components\Section::make('Points Requirements')
                    ->schema([
                        Forms\Components\TextInput::make('min_points')
                            ->required()
                            ->numeric()
                            ->default(0),
                        Forms\Components\TextInput::make('max_points')
                            ->numeric()
                            ->nullable()
                            ->helperText('Leave empty for unlimited'),
                    ])->columns(2),

                Forms\Components\Section::make('Benefits')
                    ->schema([
                        Forms\Components\TextInput::make('discount_percentage')
                            ->numeric()
                            ->default(0)
                            ->suffix('%')
                            ->minValue(0)
                            ->maxValue(100),
                        Forms\Components\TextInput::make('points_multiplier')
                            ->numeric()
                            ->default(1.0)
                            ->step(0.1)
                            ->minValue(1),
                        Forms\Components\Toggle::make('priority_booking')
                            ->default(false),
                        Forms\Components\KeyValue::make('benefits')
                            ->keyLabel('Benefit Name')
                            ->valueLabel('Description')
                            ->nullable(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color(fn ($record) => $record->badge_color),
                Tables\Columns\TextColumn::make('min_points')
                    ->label('Min Points')
                    ->sortable()
                    ->formatStateUsing(fn ($state) => number_format($state)),
                Tables\Columns\TextColumn::make('max_points')
                    ->label('Max Points')
                    ->sortable()
                    ->formatStateUsing(fn ($state) => $state ? number_format($state) : '∞'),
                Tables\Columns\TextColumn::make('discount_percentage')
                    ->label('Discount')
                    ->sortable()
                    ->formatStateUsing(fn ($state) => $state.'%'),
                Tables\Columns\TextColumn::make('points_multiplier')
                    ->label('Points Multiplier')
                    ->sortable()
                    ->formatStateUsing(fn ($state) => $state.'x'),
                Tables\Columns\IconColumn::make('priority_booking')
                    ->boolean(),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
                Tables\Columns\TextColumn::make('userLoyalties_count')
                    ->counts('userLoyalties')
                    ->label('Members'),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('order');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLoyaltyTiers::route('/'),
            'create' => Pages\CreateLoyaltyTier::route('/create'),
            'edit' => Pages\EditLoyaltyTier::route('/{record}/edit'),
        ];
    }
}
