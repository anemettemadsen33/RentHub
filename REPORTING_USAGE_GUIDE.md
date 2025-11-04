# Advanced Reporting - Usage Guide

Complete guide for using the reporting and export features.

## Quick Start

### 1. Excel Export

```php
use App\Exports\RevenueReportExport;
use Maatwebsite\Excel\Facades\Excel;

// Prepare data
$data = [
    ['month' => '2025-01', 'property' => 'Beach House', 'bookings' => 15, 'revenue' => 18000, 'avg_rate' => 1200, 'occupancy' => 85.5],
    ['month' => '2025-02', 'property' => 'Beach House', 'bookings' => 12, 'revenue' => 14400, 'avg_rate' => 1200, 'occupancy' => 75.0],
];

$summary = [
    'total_revenue' => 32400,
    'total_bookings' => 27,
    'avg_rate' => 1200,
];

// Export to Excel
return Excel::download(
    new RevenueReportExport($data, $summary), 
    'revenue-report-' . date('Y-m-d') . '.xlsx'
);
```

### 2. PDF Export

```php
use Barryvdh\DomPDF\Facade\Pdf;

// Load view with data
$pdf = Pdf::loadView('reports.revenue', [
    'data' => $data,
    'summary' => $summary,
    'dateFrom' => '2025-01-01',
    'dateTo' => '2025-12-31',
]);

// Download
return $pdf->download('revenue-report.pdf');

// Or display in browser
return $pdf->stream('revenue-report.pdf');
```

### 3. CSV Export

```php
return Excel::download(
    new RevenueReportExport($data), 
    'revenue-report.csv',
    \Maatwebsite\Excel\Excel::CSV
);
```

## Export Classes Created

### RevenueReportExport
- **Columns**: Date, Property, Bookings, Revenue, Avg Rate, Occupancy
- **Features**: Auto-sizing, bold headers, formatted numbers
- **Usage**: Revenue and financial reports

### BookingsReportExport
- **Columns**: Booking ID, Date, Guest, Property, Check-in, Check-out, Nights, Amount, Status
- **Features**: Complete booking information
- **Usage**: Booking lists and guest reports

## Controller Implementation

```php
<?php

namespace App\Http\Controllers\Api;

use App\Exports\RevenueReportExport;
use App\Exports\BookingsReportExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportExportController extends Controller
{
    /**
     * Export revenue report
     */
    public function exportRevenue(Request $request)
    {
        $format = $request->input('format', 'excel'); // excel, pdf, csv
        
        // Get report data (from your service)
        $data = $this->getRevenueData($request);
        
        switch ($format) {
            case 'pdf':
                $pdf = Pdf::loadView('reports.revenue', [
                    'data' => $data['rows'],
                    'summary' => $data['summary'],
                    'dateFrom' => $request->date_from,
                    'dateTo' => $request->date_to,
                ]);
                return $pdf->download('revenue-report.pdf');
                
            case 'csv':
                return Excel::download(
                    new RevenueReportExport($data['rows'], $data['summary']),
                    'revenue-report.csv',
                    \Maatwebsite\Excel\Excel::CSV
                );
                
            default: // excel
                return Excel::download(
                    new RevenueReportExport($data['rows'], $data['summary']),
                    'revenue-report.xlsx'
                );
        }
    }
    
    /**
     * Export bookings report
     */
    public function exportBookings(Request $request)
    {
        $format = $request->input('format', 'excel');
        $data = $this->getBookingsData($request);
        
        switch ($format) {
            case 'pdf':
                $pdf = Pdf::loadView('reports.bookings', ['data' => $data]);
                return $pdf->download('bookings-report.pdf');
                
            case 'csv':
                return Excel::download(
                    new BookingsReportExport($data),
                    'bookings-report.csv',
                    \Maatwebsite\Excel\Excel::CSV
                );
                
            default:
                return Excel::download(
                    new BookingsReportExport($data),
                    'bookings-report.xlsx'
                );
        }
    }
    
    /**
     * Get revenue data (implement based on your needs)
     */
    protected function getRevenueData(Request $request)
    {
        // Example implementation
        $bookings = Booking::query()
            ->whereBetween('check_in', [$request->date_from, $request->date_to])
            ->where('status', 'completed')
            ->with('property')
            ->get();
            
        $rows = $bookings->groupBy(function($booking) {
            return $booking->check_in->format('Y-m');
        })->map(function($monthBookings) {
            return [
                'month' => $monthBookings->first()->check_in->format('Y-m'),
                'property' => $monthBookings->first()->property->title,
                'bookings' => $monthBookings->count(),
                'revenue' => $monthBookings->sum('total_amount'),
                'avg_rate' => $monthBookings->avg('total_amount'),
                'occupancy' => 0, // Calculate based on your logic
            ];
        })->values();
        
        $summary = [
            'total_revenue' => $rows->sum('revenue'),
            'total_bookings' => $rows->sum('bookings'),
            'avg_rate' => $rows->avg('avg_rate'),
        ];
        
        return [
            'rows' => $rows->toArray(),
            'summary' => $summary,
        ];
    }
}
```

## Advanced Excel Features

### Multiple Sheets
```php
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class FullReportExport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            'Revenue' => new RevenueReportExport($revenueData),
            'Bookings' => new BookingsReportExport($bookingsData),
        ];
    }
}

// Export
Excel::download(new FullReportExport(), 'full-report.xlsx');
```

### Charts in Excel
```php
use Maatwebsite\Excel\Concerns\WithCharts;
use PhpOffice\PhpSpreadsheet\Chart\Chart;

class RevenueWithChartExport implements WithCharts
{
    public function charts()
    {
        // Configure chart (line, bar, pie, etc.)
        return [
            // Chart configuration
        ];
    }
}
```

### Styling
```php
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;

public function styles(Worksheet $sheet)
{
    return [
        // Header row styling
        1 => [
            'font' => ['bold' => true, 'size' => 14],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'color' => ['rgb' => '4a5568']
            ],
        ],
        
        // Specific cell
        'A1' => ['font' => ['italic' => true]],
    ];
}
```

## PDF Customization

### Custom Paper Size
```php
$pdf = Pdf::loadView('reports.revenue', $data)
    ->setPaper('a4', 'landscape');
```

### Set Options
```php
$pdf = Pdf::loadView('reports.revenue', $data)
    ->setOption('margin-top', 10)
    ->setOption('margin-bottom', 10)
    ->setOption('margin-left', 10)
    ->setOption('margin-right', 10);
```

### Custom Header/Footer
```php
// In your blade view
<style>
    @page {
        margin: 100px 50px;
    }
    .page-header {
        position: fixed;
        top: -80px;
        left: 0;
        right: 0;
    }
    .page-footer {
        position: fixed;
        bottom: -60px;
        left: 0;
        right: 0;
    }
</style>

<div class="page-header">
    <h2>Your Company Name</h2>
</div>

<div class="page-footer">
    Page <span class="pagenum"></span>
</div>
```

## API Integration

### Routes
```php
// routes/api.php

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/reports/export/revenue', [ReportExportController::class, 'exportRevenue']);
    Route::post('/reports/export/bookings', [ReportExportController::class, 'exportBookings']);
    Route::post('/reports/export/occupancy', [ReportExportController::class, 'exportOccupancy']);
});
```

### Frontend Usage
```javascript
const exportReport = async (type, format) => {
  const response = await fetch(`/api/reports/export/${type}`, {
    method: 'POST',
    headers: {
      'Authorization': `Bearer ${token}`,
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({
      format: format, // 'excel', 'pdf', 'csv'
      date_from: '2025-01-01',
      date_to: '2025-12-31',
      properties: [1, 2, 3]
    })
  });
  
  // Download file
  const blob = await response.blob();
  const url = window.URL.createObjectURL(blob);
  const a = document.createElement('a');
  a.href = url;
  a.download = `${type}-report.${format === 'excel' ? 'xlsx' : format}`;
  a.click();
};

// Usage
<button onClick={() => exportReport('revenue', 'excel')}>
  Export to Excel
</button>
<button onClick={() => exportReport('revenue', 'pdf')}>
  Export to PDF
</button>
<button onClick={() => exportReport('revenue', 'csv')}>
  Export to CSV
</button>
```

## Scheduled Reports

```php
// In your scheduled report command
use App\Exports\RevenueReportExport;
use Illuminate\Support\Facades\Mail;

protected function sendReport($scheduledReport)
{
    // Generate report
    $data = $this->getReportData($scheduledReport->report);
    
    // Export based on format
    $filename = 'report-' . now()->format('Y-m-d') . '.' . $scheduledReport->format;
    
    switch ($scheduledReport->format) {
        case 'pdf':
            $pdf = Pdf::loadView('reports.revenue', $data);
            $attachment = $pdf->output();
            break;
            
        case 'csv':
        case 'excel':
            Excel::store(
                new RevenueReportExport($data['rows']), 
                $filename,
                'temp'
            );
            $attachment = storage_path('app/temp/' . $filename);
            break;
    }
    
    // Send email
    Mail::to($scheduledReport->recipients)
        ->send(new ReportMail($attachment, $filename));
}
```

## Best Practices

1. **Large Datasets**: Use chunking for large exports
```php
public function chunkSize(): int
{
    return 1000;
}
```

2. **Memory Management**: Use queued exports for big reports
```php
Excel::queue(new LargeReportExport, 'reports/large.xlsx')
    ->chain([...]);
```

3. **Caching**: Cache report data when possible
```php
$data = Cache::remember('revenue-report-' . $month, 3600, function() {
    return $this->generateReportData();
});
```

4. **Validation**: Always validate input parameters
```php
$request->validate([
    'format' => 'required|in:excel,pdf,csv',
    'date_from' => 'required|date',
    'date_to' => 'required|date|after:date_from',
]);
```

## Configuration

### Excel Config (auto-generated)
Located at: `config/excel.php`

### DomPDF Config
Located at: `config/dompdf.php`

Key settings:
```php
'default_font' => 'arial',
'dpi' => 96,
'enable_php' => false,
'enable_remote' => true,
```

## Troubleshooting

### Memory Issues
Increase memory limit in php.ini or:
```php
ini_set('memory_limit', '512M');
```

### Timeout Issues
```php
set_time_limit(300); // 5 minutes
```

### Font Issues (PDF)
Use web-safe fonts or install additional fonts in DomPDF.

## Complete Example

See `TASK_4.9_ADVANCED_REPORTING_COMPLETE.md` for full implementation details.

---

**Status**: ✅ Ready for Production  
**Packages**: maatwebsite/excel 3.1.67, barryvdh/laravel-dompdf 3.1.1
