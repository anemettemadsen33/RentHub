<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class BookingsReportExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        return collect($this->data);
    }

    public function headings(): array
    {
        return [
            'Booking ID',
            'Date',
            'Guest',
            'Property',
            'Check-in',
            'Check-out',
            'Nights',
            'Amount',
            'Status',
        ];
    }

    public function map($row): array
    {
        return [
            $row['id'] ?? '-',
            $row['booking_date'] ?? '-',
            $row['guest_name'] ?? '-',
            $row['property_name'] ?? '-',
            $row['check_in'] ?? '-',
            $row['check_out'] ?? '-',
            $row['nights'] ?? 0,
            '$' . number_format($row['total_amount'] ?? 0, 2),
            ucfirst($row['status'] ?? '-'),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12]],
        ];
    }
}
