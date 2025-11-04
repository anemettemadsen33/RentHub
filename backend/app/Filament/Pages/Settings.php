<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Notifications\Notification;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Support\Enums\IconSize;

class Settings extends Page implements HasForms
{
    use InteractsWithForms;

    public string $view = 'filament.pages.settings';

    public ?array $data = [];
    
    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-cog-6-tooth';
    }
    
    public static function getNavigationLabel(): string
    {
        return 'Settings';
    }
    
    public static function getNavigationSort(): ?int
    {
        return 100;
    }

    public function mount(): void
    {
        $this->form->fill([
            'frontend_url' => Setting::get('frontend_url', 'http://localhost:3000'),
            'company_name' => Setting::get('company_name', 'RentHub'),
            'company_email' => Setting::get('company_email', 'info@renthub.com'),
            'company_phone' => Setting::get('company_phone', ''),
            'company_address' => Setting::get('company_address', ''),
            'company_google_maps' => Setting::get('company_google_maps', ''),
            'mail_mailer' => Setting::get('mail_mailer', 'smtp'),
            'mail_host' => Setting::get('mail_host', 'smtp.mailtrap.io'),
            'mail_port' => Setting::get('mail_port', '2525'),
            'mail_username' => Setting::get('mail_username', ''),
            'mail_password' => Setting::get('mail_password', ''),
            'mail_encryption' => Setting::get('mail_encryption', 'tls'),
            'mail_from_address' => Setting::get('mail_from_address', 'noreply@renthub.com'),
            'mail_from_name' => Setting::get('mail_from_name', 'RentHub'),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Frontend Settings')
                    ->description('Configure frontend application URL')
                    ->icon('heroicon-o-computer-desktop')
                    ->schema([
                    TextInput::make('frontend_url')
                        ->label('Frontend URL')
                        ->url()
                        ->required()
                        ->placeholder('https://renthub.com')
                        ->helperText('The URL where your frontend application is hosted')
                        ->columnSpanFull(),
                    ]),
                
                Section::make('Company Information')
                    ->description('Manage company details and contact information')
                    ->icon('heroicon-o-building-office')
                    ->schema([
                    TextInput::make('company_name')
                        ->label('Company Name')
                        ->required()
                        ->maxLength(255),
                    TextInput::make('company_email')
                        ->label('Company Email')
                        ->email()
                        ->required()
                        ->maxLength(255),
                    TextInput::make('company_phone')
                        ->label('Phone Number')
                        ->tel()
                        ->maxLength(255),
                    Textarea::make('company_address')
                        ->label('Address')
                        ->rows(3)
                        ->columnSpanFull(),
                    Textarea::make('company_google_maps')
                        ->label('Google Maps Embed URL')
                        ->placeholder('https://www.google.com/maps/embed?pb=...')
                        ->helperText('Paste the embed URL from Google Maps')
                        ->rows(3)
                        ->columnSpanFull(),
                    ]),
                
                Section::make('Mail Configuration')
                    ->description('Configure email settings for notifications')
                    ->icon('heroicon-o-envelope')
                    ->schema([
                    Select::make('mail_mailer')
                        ->label('Mail Driver')
                        ->options([
                            'smtp' => 'SMTP',
                            'sendmail' => 'Sendmail',
                            'mailgun' => 'Mailgun',
                            'ses' => 'Amazon SES',
                            'postmark' => 'Postmark',
                        ])
                        ->required(),
                    TextInput::make('mail_host')
                        ->label('Mail Host')
                        ->required()
                        ->maxLength(255),
                    TextInput::make('mail_port')
                        ->label('Mail Port')
                        ->required()
                        ->numeric()
                        ->maxLength(10),
                    Select::make('mail_encryption')
                        ->label('Encryption')
                        ->options([
                            'tls' => 'TLS',
                            'ssl' => 'SSL',
                            'none' => 'None',
                        ])
                        ->required(),
                    TextInput::make('mail_username')
                        ->label('Username')
                        ->maxLength(255),
                    TextInput::make('mail_password')
                        ->label('Password')
                        ->password()
                        ->revealable()
                        ->maxLength(255),
                    TextInput::make('mail_from_address')
                        ->label('From Address')
                        ->email()
                        ->required()
                        ->maxLength(255),
                    TextInput::make('mail_from_name')
                        ->label('From Name')
                        ->required()
                        ->maxLength(255),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        Setting::set('frontend_url', $data['frontend_url'], 'frontend', 'url');
        Setting::set('company_name', $data['company_name'], 'company', 'text');
        Setting::set('company_email', $data['company_email'], 'company', 'email');
        Setting::set('company_phone', $data['company_phone'], 'company', 'tel');
        Setting::set('company_address', $data['company_address'], 'company', 'textarea');
        Setting::set('company_google_maps', $data['company_google_maps'], 'company', 'textarea');
        Setting::set('mail_mailer', $data['mail_mailer'], 'mail', 'text');
        Setting::set('mail_host', $data['mail_host'], 'mail', 'text');
        Setting::set('mail_port', $data['mail_port'], 'mail', 'text');
        Setting::set('mail_encryption', $data['mail_encryption'], 'mail', 'text');
        Setting::set('mail_username', $data['mail_username'], 'mail', 'text');
        Setting::set('mail_password', $data['mail_password'], 'mail', 'password');
        Setting::set('mail_from_address', $data['mail_from_address'], 'mail', 'email');
        Setting::set('mail_from_name', $data['mail_from_name'], 'mail', 'text');

        Notification::make()
            ->success()
            ->title('Settings saved')
            ->body('Your settings have been saved successfully.')
            ->send();
    }
}
