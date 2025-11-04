<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Revenue Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #4a5568;
            padding-bottom: 20px;
        }
        .header h1 {
            color: #2d3748;
            margin: 0;
            font-size: 24px;
        }
        .header .period {
            color: #718096;
            margin-top: 5px;
        }
        .summary {
            background: #f7fafc;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
        }
        .summary-item {
            text-align: center;
        }
        .summary-label {
            color: #718096;
            font-size: 11px;
            text-transform: uppercase;
            margin-bottom: 5px;
        }
        .summary-value {
            color: #2d3748;
            font-size: 20px;
            font-weight: bold;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th {
            background: #4a5568;
            color: white;
            padding: 10px;
            text-align: left;
            font-weight: 600;
        }
        td {
            padding: 8px 10px;
            border-bottom: 1px solid #e2e8f0;
        }
        tr:nth-child(even) {
            background: #f7fafc;
        }
        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #e2e8f0;
            text-align: center;
            font-size: 10px;
            color: #a0aec0;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Revenue Report</h1>
        <div class="period">
            {{ $dateFrom ?? 'N/A' }} - {{ $dateTo ?? 'N/A' }}
        </div>
    </div>

    @if(isset($summary))
    <div class="summary">
        <div class="summary-grid">
            <div class="summary-item">
                <div class="summary-label">Total Revenue</div>
                <div class="summary-value">${{ number_format($summary['total_revenue'] ?? 0, 2) }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Total Bookings</div>
                <div class="summary-value">{{ $summary['total_bookings'] ?? 0 }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Average Rate</div>
                <div class="summary-value">${{ number_format($summary['avg_rate'] ?? 0, 2) }}</div>
            </div>
        </div>
    </div>
    @endif

    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Property</th>
                <th>Bookings</th>
                <th>Revenue</th>
                <th>Avg Rate</th>
                <th>Occupancy</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $row)
            <tr>
                <td>{{ $row['date'] ?? $row['month'] ?? '-' }}</td>
                <td>{{ $row['property_name'] ?? $row['property'] ?? '-' }}</td>
                <td>{{ $row['bookings'] ?? 0 }}</td>
                <td>${{ number_format($row['revenue'] ?? 0, 2) }}</td>
                <td>${{ number_format($row['avg_rate'] ?? 0, 2) }}</td>
                <td>{{ number_format($row['occupancy'] ?? 0, 1) }}%</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Generated on {{ now()->format('Y-m-d H:i') }} | RentHub Business Intelligence
    </div>
</body>
</html>
