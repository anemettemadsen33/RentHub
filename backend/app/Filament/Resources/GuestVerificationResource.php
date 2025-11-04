<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GuestVerificationResource\Pages;
use App\Models\GuestVerification;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists;
use Illuminate\Database\Eloquent\Builder;

class GuestVerificationResource extends Resource
{
    protected static ?string $model = GuestVerification::class;
    
    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-o-shield-check';
    }
    
    public static function getNavigationLabel(): string
    {
        return 'Guest Verifications';
    }
    
    public static function getNavigationSort(): ?int
    {
        return 3;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\Section::make('Guest Information')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->required()
                            ->disabled(fn ($record) => $record !== null),
                    ])->columns(1),

                Forms\Components\Section::make('Identity Verification')
                    ->schema([
                        Forms\Components\Select::make('identity_status')
                            ->options([
                                'pending' => 'Pending',
                                'verified' => 'Verified',
                                'rejected' => 'Rejected',
                                'expired' => 'Expired',
                            ])
                            ->required(),
                        Forms\Components\Select::make('document_type')
                            ->options([
                                'passport' => 'Passport',
                                'drivers_license' => 'Driver\'s License',
                                'id_card' => 'ID Card',
                                'national_id' => 'National ID',
                            ]),
                        Forms\Components\TextInput::make('document_number')
                            ->maxLength(50),
                        Forms\Components\DatePicker::make('document_expiry_date'),
                        Forms\Components\FileUpload::make('document_front')
                            ->image()
                            ->directory('verifications/identity')
                            ->visibility('public')
                            ->downloadable()
                            ->openable(),
                        Forms\Components\FileUpload::make('document_back')
                            ->image()
                            ->directory('verifications/identity')
                            ->visibility('public')
                            ->downloadable()
                            ->openable(),
                        Forms\Components\FileUpload::make('selfie_photo')
                            ->image()
                            ->directory('verifications/selfie')
                            ->visibility('public')
                            ->downloadable()
                            ->openable(),
                        Forms\Components\DateTimePicker::make('identity_verified_at'),
                        Forms\Components\Textarea::make('identity_rejection_reason')
                            ->rows(3)
                            ->visible(fn ($get) => $get('identity_status') === 'rejected'),
                    ])->columns(2),

                Forms\Components\Section::make('Background Check')
                    ->schema([
                        Forms\Components\Select::make('background_status')
                            ->options([
                                'pending' => 'Pending',
                                'clear' => 'Clear',
                                'flagged' => 'Flagged',
                            ])
                            ->required(),
                        Forms\Components\Textarea::make('background_notes')
                            ->rows(3),
                        Forms\Components\DateTimePicker::make('background_checked_at'),
                    ])->columns(2),

                Forms\Components\Section::make('Credit Check')
                    ->schema([
                        Forms\Components\Toggle::make('credit_check_enabled')
                            ->label('Credit Check Enabled'),
                        Forms\Components\Select::make('credit_status')
                            ->options([
                                'not_requested' => 'Not Requested',
                                'pending' => 'Pending',
                                'approved' => 'Approved',
                                'rejected' => 'Rejected',
                            ])
                            ->required(),
                        Forms\Components\TextInput::make('credit_score')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(850),
                        Forms\Components\Textarea::make('credit_report')
                            ->rows(3),
                        Forms\Components\DateTimePicker::make('credit_checked_at'),
                    ])->columns(2),

                Forms\Components\Section::make('Trust Score & Statistics')
                    ->schema([
                        Forms\Components\TextInput::make('trust_score')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(5)
                            ->step(0.01)
                            ->disabled(),
                        Forms\Components\TextInput::make('completed_bookings')
                            ->numeric()
                            ->minValue(0)
                            ->default(0),
                        Forms\Components\TextInput::make('cancelled_bookings')
                            ->numeric()
                            ->minValue(0)
                            ->default(0),
                        Forms\Components\TextInput::make('positive_reviews')
                            ->numeric()
                            ->minValue(0)
                            ->default(0),
                        Forms\Components\TextInput::make('negative_reviews')
                            ->numeric()
                            ->minValue(0)
                            ->default(0),
                        Forms\Components\TextInput::make('references_verified')
                            ->numeric()
                            ->minValue(0)
                            ->default(0)
                            ->disabled(),
                    ])->columns(3),

                Forms\Components\Section::make('Admin Notes')
                    ->schema([
                        Forms\Components\Textarea::make('admin_notes')
                            ->rows(3),
                        Forms\Components\Select::make('verified_by')
                            ->relationship('verifiedBy', 'name')
                            ->searchable(),
                    ])->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Guest Name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.email')
                    ->label('Email')
                    ->searchable()
                    ->copyable(),
                Tables\Columns\BadgeColumn::make('identity_status')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'verified',
                        'danger' => 'rejected',
                        'secondary' => 'expired',
                    ]),
                Tables\Columns\BadgeColumn::make('background_status')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'clear',
                        'danger' => 'flagged',
                    ]),
                Tables\Columns\BadgeColumn::make('credit_status')
                    ->colors([
                        'secondary' => 'not_requested',
                        'warning' => 'pending',
                        'success' => 'approved',
                        'danger' => 'rejected',
                    ]),
                Tables\Columns\TextColumn::make('trust_score')
                    ->label('Trust Score')
                    ->badge()
                    ->color(fn ($state) => match(true) {
                        $state >= 4.5 => 'success',
                        $state >= 3.5 => 'primary',
                        $state >= 2.5 => 'warning',
                        default => 'danger',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('completed_bookings')
                    ->label('Bookings')
                    ->sortable(),
                Tables\Columns\TextColumn::make('references_verified')
                    ->label('References')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_verified')
                    ->label('Verified')
                    ->boolean()
                    ->getStateUsing(fn ($record) => $record->isFullyVerified()),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('identity_status')
                    ->options([
                        'pending' => 'Pending',
                        'verified' => 'Verified',
                        'rejected' => 'Rejected',
                        'expired' => 'Expired',
                    ]),
                Tables\Filters\SelectFilter::make('background_status')
                    ->options([
                        'pending' => 'Pending',
                        'clear' => 'Clear',
                        'flagged' => 'Flagged',
                    ]),
                Tables\Filters\Filter::make('high_trust')
                    ->query(fn (Builder $query) => $query->where('trust_score', '>=', 4.0))
                    ->label('High Trust Score (4.0+)'),
                Tables\Filters\Filter::make('fully_verified')
                    ->query(fn (Builder $query) => $query->where('identity_status', 'verified')
                        ->where('background_status', 'clear'))
                    ->label('Fully Verified'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('approve_identity')
                    ->label('Approve Identity')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn ($record) => $record->identity_status === 'pending')
                    ->action(function ($record) {
                        $record->update([
                            'identity_status' => 'verified',
                            'identity_verified_at' => now(),
                            'verified_by' => auth()->id(),
                        ]);
                        $record->updateTrustScore();
                        \App\Models\VerificationLog::log(
                            $record->id,
                            'identity',
                            'approved',
                            'Identity approved by admin',
                            auth()->id()
                        );
                    }),
                Tables\Actions\Action::make('reject_identity')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->form([
                        Forms\Components\Textarea::make('reason')
                            ->label('Rejection Reason')
                            ->required()
                            ->rows(3),
                    ])
                    ->visible(fn ($record) => $record->identity_status === 'pending')
                    ->action(function ($record, array $data) {
                        $record->update([
                            'identity_status' => 'rejected',
                            'identity_rejection_reason' => $data['reason'],
                            'verified_by' => auth()->id(),
                        ]);
                        \App\Models\VerificationLog::log(
                            $record->id,
                            'identity',
                            'rejected',
                            $data['reason'],
                            auth()->id()
                        );
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Infolists\Components\Section::make('Guest Information')
                    ->schema([
                        Infolists\Components\TextEntry::make('user.name')
                            ->label('Name'),
                        Infolists\Components\TextEntry::make('user.email')
                            ->label('Email'),
                        Infolists\Components\TextEntry::make('trust_score')
                            ->badge()
                            ->color(fn ($state) => match(true) {
                                $state >= 4.5 => 'success',
                                $state >= 3.5 => 'primary',
                                $state >= 2.5 => 'warning',
                                default => 'danger',
                            }),
                    ])->columns(3),

                Infolists\Components\Section::make('Verification Status')
                    ->schema([
                        Infolists\Components\TextEntry::make('identity_status')
                            ->badge(),
                        Infolists\Components\TextEntry::make('background_status')
                            ->badge(),
                        Infolists\Components\TextEntry::make('credit_status')
                            ->badge(),
                        Infolists\Components\IconEntry::make('is_verified')
                            ->label('Fully Verified')
                            ->boolean()
                            ->getStateUsing(fn ($record) => $record->isFullyVerified()),
                    ])->columns(4),

                Infolists\Components\Section::make('Documents')
                    ->schema([
                        Infolists\Components\ImageEntry::make('document_front')
                            ->label('Document Front'),
                        Infolists\Components\ImageEntry::make('document_back')
                            ->label('Document Back'),
                        Infolists\Components\ImageEntry::make('selfie_photo')
                            ->label('Selfie'),
                    ])->columns(3),

                Infolists\Components\Section::make('Statistics')
                    ->schema([
                        Infolists\Components\TextEntry::make('completed_bookings'),
                        Infolists\Components\TextEntry::make('cancelled_bookings'),
                        Infolists\Components\TextEntry::make('positive_reviews'),
                        Infolists\Components\TextEntry::make('negative_reviews'),
                        Infolists\Components\TextEntry::make('references_verified'),
                    ])->columns(5),
            ]);
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
            'index' => Pages\ListGuestVerifications::route('/'),
            'create' => Pages\CreateGuestVerification::route('/create'),
            'view' => Pages\ViewGuestVerification::route('/{record}'),
            'edit' => Pages\EditGuestVerification::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('identity_status', 'pending')->count();
    }
}
