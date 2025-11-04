<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RevenueReportExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping, WithStyles
{
    protected $data;

    protected $summary;

    public function __construct($data, $summary = null)
    {
        $this->data = $data;
        $this->summary = $summary;
    }

    public function collection()
    {
        return collect($this->data);
    }

    public function headings(): array
    {
        return [
            'Date',
            'Property',
            'Bookings',
            'Revenue',
            'Average Rate',
            'Occupancy %',
        ];
    }

    public function map($row): array
    {
        return [
            $row['date'] ?? $row['month'] ?? '-',
            $row['property_name'] ?? $row['property'] ?? '-',
            $row['bookings'] ?? 0,
            '$'.number_format($row['revenue'] ?? 0, 2),
            '$'.number_format($row['avg_rate'] ?? 0, 2),
            number_format($row['occupancy'] ?? 0, 1).'%',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12]],
        ];
    }
}
