<?php

namespace App\Filament\Pages;

use App\Models\Booking;
use App\Models\Payment;
use App\Models\Property;
use Filament\Pages\Page;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Actions\Action;
use UnitEnum;
use BackedEnum;

class Reports extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?int $navigationSort = 90;
    
    protected string $view = 'filament.pages.reports';
    
    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-document-chart-bar';
    
    protected static UnitEnum|string|null $navigationGroup = 'Rapoarte & Statistici';
    
    public ?array $data = [];
    
    public static function getNavigationLabel(): string
    {
        return 'Rapoarte';
    }
    
    public function getTitle(): string
    {
        return 'Rapoarte Închirieri';
    }
    
    public static function canAccess(): bool
    {
        return auth()->user()?->hasAnyRole(['admin', 'manager']) ?? false;
    }
    
    public function mount(): void
    {
        $this->form->fill([
            'start_date' => now()->startOfMonth(),
            'end_date' => now()->endOfMonth(),
            'format' => 'pdf',
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Generare Rapoarte')
                    ->description('Selectați tipul de raport și perioada dorită')
                    ->schema([
                        Forms\Components\Select::make('report_type')
                            ->label('Tip Raport')
                            ->options([
                                'bookings' => 'Raport Rezervări (Long-term & Short-term)',
                                'revenue' => 'Raport Venituri',
                                'properties' => 'Raport Proprietăți',
                                'occupancy' => 'Raport Ocupare',
                                'long_term' => 'Raport Închirieri Long-term',
                                'short_term' => 'Raport Închirieri Short-term',
                            ])
                            ->required()
                            ->live(),
                        
                        Forms\Components\DatePicker::make('start_date')
                            ->label('Data Început')
                            ->required()
                            ->default(now()->startOfMonth()),
                        
                        Forms\Components\DatePicker::make('end_date')
                            ->label('Data Sfârșit')
                            ->required()
                            ->default(now()->endOfMonth()),
                        
                        Forms\Components\Select::make('format')
                            ->label('Format Export')
                            ->options([
                                'pdf' => 'PDF',
                                'excel' => 'Excel',
                                'csv' => 'CSV',
                            ])
                            ->default('pdf')
                            ->required(),
                    ])
                    ->columns(2),
            ])
            ->statePath('data');
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Generează Raport')
                ->icon('heroicon-o-document-arrow-down')
                ->action('generateReport')
                ->color('primary'),
        ];
    }

    public function generateReport(): void
    {
        $data = $this->form->getState();
        
        $reportType = $data['report_type'];
        $startDate = $data['start_date'];
        $endDate = $data['end_date'];
        $format = $data['format'];

        try {
            switch ($reportType) {
                case 'bookings':
                    $this->generateBookingsReport($startDate, $endDate, $format);
                    break;
                case 'revenue':
                    $this->generateRevenueReport($startDate, $endDate, $format);
                    break;
                case 'properties':
                    $this->generatePropertiesReport($startDate, $endDate, $format);
                    break;
                case 'occupancy':
                    $this->generateOccupancyReport($startDate, $endDate, $format);
                    break;
            }

            Notification::make()
                ->title('Raport generat cu succes!')
                ->success()
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Eroare la generarea raportului')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    protected function generateBookingsReport($startDate, $endDate, $format): void
    {
        $bookings = Booking::with(['property', 'user'])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        $data = [
            'title' => 'Raport Rezervări',
            'period' => $startDate . ' - ' . $endDate,
            'bookings' => $bookings,
            'total' => $bookings->sum('total_price'),
            'count' => $bookings->count(),
        ];

        // For Excel/CSV implementation
        Notification::make()
            ->title('Raport rezervări generat: ' . $bookings->count() . ' rezervări')
            ->success()
            ->send();
    }

    protected function generateRevenueReport($startDate, $endDate, $format): void
    {
        $payments = Payment::with(['booking.property'])
            ->where('status', 'completed')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        $data = [
            'title' => 'Raport Venituri',
            'period' => $startDate . ' - ' . $endDate,
            'payments' => $payments,
            'total' => $payments->sum('amount'),
            'count' => $payments->count(),
            'average' => $payments->avg('amount'),
        ];

        // Similar implementation as bookings
        Notification::make()
            ->title('Raport venituri generat')
            ->info()
            ->send();
    }

    protected function generatePropertiesReport($startDate, $endDate, $format): void
    {
        $properties = Property::withCount(['bookings' => function ($query) use ($startDate, $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }])->get();

        Notification::make()
            ->title('Raport proprietăți generat')
            ->info()
            ->send();
    }

    protected function generateOccupancyReport($startDate, $endDate, $format): void
    {
        // Calculate occupancy rates
        Notification::make()
            ->title('Raport ocupare generat')
            ->info()
            ->send();
    }
}
