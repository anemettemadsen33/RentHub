<?php

namespace App\Filament\Resources\UserVerifications\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Fieldset;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Get;

class UserVerificationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('User Information')
                    ->schema([
                        Select::make('user_id')
                            ->label('User')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->columnSpanFull(),
                    ]),
                
                Section::make('ID Verification')
                    ->description('Identity document verification')
                    ->schema([
                        Select::make('id_verification_status')
                            ->label('Verification Status')
                            ->options([
                                'pending' => 'Pending',
                                'under_review' => 'Under Review',
                                'approved' => 'Approved',
                                'rejected' => 'Rejected',
                            ])
                            ->required()
                            ->default('pending')
                            ->reactive(),
                        
                        Select::make('id_document_type')
                            ->label('Document Type')
                            ->options([
                                'passport' => 'Passport',
                                'driving_license' => 'Driving License',
                                'national_id' => 'National ID',
                            ])
                            ->required(),
                        
                        TextInput::make('id_document_number')
                            ->label('Document Number')
                            ->maxLength(255),
                        
                        Grid::make(3)
                            ->schema([
                                FileUpload::make('id_front_image')
                                    ->label('ID Front')
                                    ->image()
                                    ->directory('verifications/id')
                                    ->visibility('private')
                                    ->maxSize(5120),
                                
                                FileUpload::make('id_back_image')
                                    ->label('ID Back')
                                    ->image()
                                    ->directory('verifications/id')
                                    ->visibility('private')
                                    ->maxSize(5120),
                                
                                FileUpload::make('selfie_image')
                                    ->label('Selfie Verification')
                                    ->image()
                                    ->directory('verifications/selfie')
                                    ->visibility('private')
                                    ->maxSize(5120),
                            ]),
                        
                        DateTimePicker::make('id_verified_at')
                            ->label('Verified At')
                            ->disabled(),
                        
                        Textarea::make('id_rejection_reason')
                            ->label('Rejection Reason')
                            ->visible(fn (Get $get) => $get('id_verification_status') === 'rejected')
                            ->columnSpanFull()
                            ->rows(3),
                    ])
                    ->columns(2)
                    ->collapsible(),
                
                Section::make('Phone Verification')
                    ->schema([
                        Select::make('phone_verification_status')
                            ->label('Status')
                            ->options([
                                'pending' => 'Pending',
                                'verified' => 'Verified',
                            ])
                            ->required()
                            ->default('pending'),
                        
                        TextInput::make('phone_number')
                            ->label('Phone Number')
                            ->tel()
                            ->maxLength(20),
                        
                        DateTimePicker::make('phone_verified_at')
                            ->label('Verified At')
                            ->disabled(),
                    ])
                    ->columns(3)
                    ->collapsible(),
                
                Section::make('Email Verification')
                    ->schema([
                        Select::make('email_verification_status')
                            ->label('Status')
                            ->options([
                                'pending' => 'Pending',
                                'verified' => 'Verified',
                            ])
                            ->required()
                            ->default('pending'),
                        
                        DateTimePicker::make('email_verified_at')
                            ->label('Verified At')
                            ->disabled(),
                    ])
                    ->columns(2)
                    ->collapsible(),
                
                Section::make('Address Verification')
                    ->schema([
                        Select::make('address_verification_status')
                            ->label('Status')
                            ->options([
                                'pending' => 'Pending',
                                'under_review' => 'Under Review',
                                'approved' => 'Approved',
                                'rejected' => 'Rejected',
                            ])
                            ->required()
                            ->default('pending')
                            ->reactive(),
                        
                        Textarea::make('address')
                            ->label('Address')
                            ->columnSpanFull()
                            ->rows(2),
                        
                        Select::make('address_proof_document')
                            ->label('Proof Document Type')
                            ->options([
                                'utility_bill' => 'Utility Bill',
                                'bank_statement' => 'Bank Statement',
                                'rental_agreement' => 'Rental Agreement',
                                'government_letter' => 'Government Letter',
                            ]),
                        
                        FileUpload::make('address_proof_image')
                            ->label('Proof Document')
                            ->image()
                            ->directory('verifications/address')
                            ->visibility('private')
                            ->maxSize(5120),
                        
                        DateTimePicker::make('address_verified_at')
                            ->label('Verified At')
                            ->disabled(),
                        
                        Textarea::make('address_rejection_reason')
                            ->label('Rejection Reason')
                            ->visible(fn (Get $get) => $get('address_verification_status') === 'rejected')
                            ->columnSpanFull()
                            ->rows(3),
                    ])
                    ->columns(2)
                    ->collapsible(),
                
                Section::make('Background Check')
                    ->description('Optional background verification')
                    ->schema([
                        Select::make('background_check_status')
                            ->label('Status')
                            ->options([
                                'not_requested' => 'Not Requested',
                                'pending' => 'Pending',
                                'in_progress' => 'In Progress',
                                'completed' => 'Completed',
                                'failed' => 'Failed',
                            ])
                            ->required()
                            ->default('not_requested'),
                        
                        TextInput::make('background_check_provider')
                            ->label('Provider')
                            ->maxLength(255),
                        
                        TextInput::make('background_check_reference')
                            ->label('Reference Number')
                            ->maxLength(255),
                        
                        DateTimePicker::make('background_check_completed_at')
                            ->label('Completed At')
                            ->disabled(),
                        
                        Textarea::make('background_check_result')
                            ->label('Result (JSON)')
                            ->columnSpanFull()
                            ->rows(4),
                    ])
                    ->columns(2)
                    ->collapsible()
                    ->collapsed(),
                
                Section::make('Overall Status')
                    ->schema([
                        Select::make('overall_status')
                            ->label('Overall Status')
                            ->options([
                                'unverified' => 'Unverified',
                                'partially_verified' => 'Partially Verified',
                                'fully_verified' => 'Fully Verified',
                            ])
                            ->required()
                            ->default('unverified'),
                        
                        TextInput::make('verification_score')
                            ->label('Verification Score (0-100)')
                            ->numeric()
                            ->required()
                            ->default(0)
                            ->minValue(0)
                            ->maxValue(100)
                            ->suffix('%'),
                    ])
                    ->columns(2),
                
                Section::make('Admin Review')
                    ->schema([
                        Select::make('reviewed_by')
                            ->label('Reviewed By')
                            ->relationship('reviewer', 'name')
                            ->searchable()
                            ->preload(),
                        
                        Textarea::make('admin_notes')
                            ->label('Admin Notes')
                            ->columnSpanFull()
                            ->rows(4),
                    ])
                    ->columns(1)
                    ->collapsible()
                    ->collapsed(),
            ]);
    }
}
