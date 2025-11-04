<?php

namespace App\Filament\Resources\PropertyVerifications\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Get;
use Filament\Schemas\Schema;

class PropertyVerificationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Property & Owner Information')
                    ->schema([
                        Select::make('property_id')
                            ->label('Property')
                            ->relationship('property', 'title')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->columnSpan(1),

                        Select::make('user_id')
                            ->label('Owner')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->columnSpan(1),
                    ])
                    ->columns(2),

                Section::make('Ownership Verification')
                    ->description('Verify property ownership documents')
                    ->schema([
                        Select::make('ownership_status')
                            ->label('Ownership Status')
                            ->options([
                                'pending' => 'Pending',
                                'under_review' => 'Under Review',
                                'approved' => 'Approved',
                                'rejected' => 'Rejected',
                            ])
                            ->required()
                            ->default('pending')
                            ->reactive(),

                        Select::make('ownership_document_type')
                            ->label('Document Type')
                            ->options([
                                'deed' => 'Property Deed',
                                'lease_agreement' => 'Lease Agreement',
                                'rental_contract' => 'Rental Contract',
                                'title_certificate' => 'Title Certificate',
                            ]),

                        DateTimePicker::make('ownership_verified_at')
                            ->label('Verified At')
                            ->disabled(),

                        Textarea::make('ownership_rejection_reason')
                            ->label('Rejection Reason')
                            ->visible(fn (Get $get) => $get('ownership_status') === 'rejected')
                            ->columnSpanFull()
                            ->rows(3),
                    ])
                    ->columns(2)
                    ->collapsible(),

                Section::make('Property Inspection')
                    ->description('Physical property inspection details')
                    ->schema([
                        Select::make('inspection_status')
                            ->label('Inspection Status')
                            ->options([
                                'not_required' => 'Not Required',
                                'pending' => 'Pending',
                                'scheduled' => 'Scheduled',
                                'completed' => 'Completed',
                                'failed' => 'Failed',
                            ])
                            ->required()
                            ->default('not_required')
                            ->reactive(),

                        Select::make('inspector_id')
                            ->label('Inspector')
                            ->relationship('inspector', 'name')
                            ->searchable()
                            ->preload()
                            ->visible(fn (Get $get) => in_array($get('inspection_status'), ['scheduled', 'completed'])),

                        DateTimePicker::make('inspection_scheduled_at')
                            ->label('Scheduled At')
                            ->visible(fn (Get $get) => $get('inspection_status') === 'scheduled'),

                        DateTimePicker::make('inspection_completed_at')
                            ->label('Completed At')
                            ->disabled()
                            ->visible(fn (Get $get) => $get('inspection_status') === 'completed'),

                        TextInput::make('inspection_score')
                            ->label('Inspection Score (0-100)')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->suffix('%')
                            ->visible(fn (Get $get) => $get('inspection_status') === 'completed'),

                        Textarea::make('inspection_notes')
                            ->label('Inspection Notes')
                            ->columnSpanFull()
                            ->rows(4)
                            ->visible(fn (Get $get) => in_array($get('inspection_status'), ['completed', 'failed'])),
                    ])
                    ->columns(2)
                    ->collapsible(),

                Section::make('Photos Verification')
                    ->schema([
                        Select::make('photos_status')
                            ->label('Photos Status')
                            ->options([
                                'pending' => 'Pending',
                                'approved' => 'Approved',
                                'rejected' => 'Rejected',
                            ])
                            ->required()
                            ->default('pending')
                            ->reactive(),

                        DateTimePicker::make('photos_verified_at')
                            ->label('Verified At')
                            ->disabled(),

                        Textarea::make('photos_rejection_reason')
                            ->label('Rejection Reason')
                            ->visible(fn (Get $get) => $get('photos_status') === 'rejected')
                            ->columnSpanFull()
                            ->rows(3),
                    ])
                    ->columns(2)
                    ->collapsible(),

                Section::make('Property Details Verification')
                    ->schema([
                        Select::make('details_status')
                            ->label('Details Status')
                            ->options([
                                'pending' => 'Pending',
                                'approved' => 'Approved',
                                'rejected' => 'Rejected',
                            ])
                            ->required()
                            ->default('pending')
                            ->reactive(),

                        DateTimePicker::make('details_verified_at')
                            ->label('Verified At')
                            ->disabled(),

                        Textarea::make('details_to_correct')
                            ->label('Details to Correct (JSON)')
                            ->visible(fn (Get $get) => $get('details_status') === 'rejected')
                            ->columnSpanFull()
                            ->rows(4)
                            ->helperText('Enter corrections needed in JSON format'),
                    ])
                    ->columns(2)
                    ->collapsible(),

                Section::make('Legal Compliance')
                    ->description('Business licenses and certificates')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                Toggle::make('has_business_license')
                                    ->label('Has Business License')
                                    ->inline(false),

                                Toggle::make('has_safety_certificate')
                                    ->label('Has Safety Certificate')
                                    ->inline(false),

                                Toggle::make('has_insurance')
                                    ->label('Has Insurance')
                                    ->inline(false)
                                    ->reactive(),
                            ]),

                        FileUpload::make('business_license_document')
                            ->label('Business License Document')
                            ->directory('verifications/business-licenses')
                            ->visibility('private')
                            ->visible(fn (Get $get) => $get('has_business_license')),

                        FileUpload::make('safety_certificate_document')
                            ->label('Safety Certificate Document')
                            ->directory('verifications/safety-certificates')
                            ->visibility('private')
                            ->visible(fn (Get $get) => $get('has_safety_certificate')),

                        FileUpload::make('insurance_document')
                            ->label('Insurance Document')
                            ->directory('verifications/insurance')
                            ->visibility('private')
                            ->visible(fn (Get $get) => $get('has_insurance')),

                        DatePicker::make('insurance_expiry_date')
                            ->label('Insurance Expiry Date')
                            ->visible(fn (Get $get) => $get('has_insurance')),
                    ])
                    ->columns(2)
                    ->collapsible(),

                Section::make('Overall Status')
                    ->schema([
                        Select::make('overall_status')
                            ->label('Overall Status')
                            ->options([
                                'unverified' => 'Unverified',
                                'under_review' => 'Under Review',
                                'verified' => 'Verified',
                                'rejected' => 'Rejected',
                            ])
                            ->required()
                            ->default('unverified'),

                        Toggle::make('has_verified_badge')
                            ->label('Display Verified Badge')
                            ->inline(false)
                            ->helperText('Show verified badge on property listing'),

                        TextInput::make('verification_score')
                            ->label('Verification Score (0-100)')
                            ->numeric()
                            ->required()
                            ->default(0)
                            ->minValue(0)
                            ->maxValue(100)
                            ->suffix('%'),
                    ])
                    ->columns(3),

                Section::make('Review & Re-verification')
                    ->schema([
                        Select::make('reviewed_by')
                            ->label('Reviewed By')
                            ->relationship('reviewer', 'name')
                            ->searchable()
                            ->preload(),

                        DateTimePicker::make('reviewed_at')
                            ->label('Reviewed At')
                            ->disabled(),

                        DatePicker::make('next_verification_due')
                            ->label('Next Verification Due')
                            ->helperText('When should this property be re-verified?'),

                        DateTimePicker::make('last_verified_at')
                            ->label('Last Verified At')
                            ->disabled(),

                        Textarea::make('admin_notes')
                            ->label('Admin Notes')
                            ->columnSpanFull()
                            ->rows(4),
                    ])
                    ->columns(2)
                    ->collapsible()
                    ->collapsed(),
            ]);
    }
}
