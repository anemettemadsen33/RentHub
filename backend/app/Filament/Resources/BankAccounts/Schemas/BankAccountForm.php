<?php

namespace App\Filament\Resources\BankAccounts\Schemas;

use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Grid;
use Filament\Schemas\Schema;

class BankAccountForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Account Type')
                    ->description('Choose if this is a company account or agent/owner account')
                    ->schema([
                        Select::make('user_id')
                            ->label('Agent/Owner')
                            ->relationship('user', 'name', fn ($query) => $query->whereIn('role', ['owner', 'agent']))
                            ->searchable()
                            ->placeholder('Leave empty for company account')
                            ->helperText('Select an agent/owner or leave empty for company account')
                            ->columnSpanFull(),
                    ]),

                Section::make('Account Information')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('account_name')
                                    ->label('Account Name')
                                    ->required()
                                    ->maxLength(255)
                                    ->helperText('Company name or account holder name'),
                                
                                TextInput::make('account_holder_name')
                                    ->label('Account Holder Name')
                                    ->required()
                                    ->maxLength(255)
                                    ->helperText('Legal name of the account holder'),
                            ]),

                        Grid::make(2)
                            ->schema([
                                Select::make('account_type')
                                    ->label('Account Type')
                                    ->options([
                                        'business' => 'Business',
                                        'personal' => 'Personal',
                                    ])
                                    ->default('business')
                                    ->required(),

                                Select::make('currency')
                                    ->label('Currency')
                                    ->options([
                                        'EUR' => 'EUR - Euro',
                                        'USD' => 'USD - US Dollar',
                                        'GBP' => 'GBP - British Pound',
                                        'RON' => 'RON - Romanian Leu',
                                    ])
                                    ->default('EUR')
                                    ->searchable()
                                    ->required(),
                            ]),
                    ]),

                Section::make('Bank Details')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('iban')
                                    ->label('IBAN')
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(34)
                                    ->placeholder('RO49AAAA1B31007593840000')
                                    ->helperText('International Bank Account Number'),
                                
                                TextInput::make('bic_swift')
                                    ->label('BIC/SWIFT Code')
                                    ->required()
                                    ->maxLength(11)
                                    ->placeholder('AAAROBU')
                                    ->helperText('Bank Identifier Code'),
                            ]),

                        TextInput::make('bank_name')
                            ->label('Bank Name')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('e.g., ING Bank Romania')
                            ->columnSpanFull(),

                        Textarea::make('bank_address')
                            ->label('Bank Address')
                            ->maxLength(500)
                            ->rows(2)
                            ->columnSpanFull(),
                    ]),

                Section::make('Status & Settings')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Toggle::make('is_active')
                                    ->label('Active')
                                    ->default(true)
                                    ->helperText('Can this account be used for transactions?')
                                    ->required(),
                                
                                Toggle::make('is_default')
                                    ->label('Set as Default')
                                    ->default(false)
                                    ->helperText('Use this account as default for invoices')
                                    ->required(),
                            ]),

                        Textarea::make('notes')
                            ->label('Notes')
                            ->maxLength(1000)
                            ->rows(3)
                            ->placeholder('Add any internal notes about this account...')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
