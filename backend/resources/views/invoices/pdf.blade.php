<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            color: #333;
            line-height: 1.6;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 40px;
        }
        .header {
            margin-bottom: 40px;
            border-bottom: 3px solid #2563eb;
            padding-bottom: 20px;
        }
        .company-info {
            float: left;
            width: 50%;
        }
        .company-info h1 {
            font-size: 28px;
            color: #2563eb;
            margin-bottom: 10px;
        }
        .invoice-info {
            float: right;
            width: 50%;
            text-align: right;
        }
        .invoice-info h2 {
            font-size: 24px;
            color: #666;
            margin-bottom: 10px;
        }
        .invoice-number {
            font-size: 16px;
            font-weight: bold;
            color: #2563eb;
        }
        .clearfix::after {
            content: "";
            display: table;
            clear: both;
        }
        .parties {
            margin: 40px 0;
        }
        .party {
            float: left;
            width: 50%;
        }
        .party h3 {
            font-size: 14px;
            color: #2563eb;
            margin-bottom: 10px;
            text-transform: uppercase;
        }
        .party p {
            margin: 5px 0;
        }
        .details {
            margin: 40px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th {
            background-color: #2563eb;
            color: white;
            padding: 12px;
            text-align: left;
            font-weight: 600;
        }
        td {
            padding: 12px;
            border-bottom: 1px solid #e5e7eb;
        }
        tr:hover {
            background-color: #f9fafb;
        }
        .text-right {
            text-align: right;
        }
        .totals {
            float: right;
            width: 300px;
            margin-top: 20px;
        }
        .totals table {
            margin: 0;
        }
        .totals td {
            padding: 8px;
        }
        .totals .total-row {
            background-color: #2563eb;
            color: white;
            font-weight: bold;
            font-size: 16px;
        }
        .bank-details {
            margin-top: 60px;
            padding: 20px;
            background-color: #f9fafb;
            border-left: 4px solid #2563eb;
        }
        .bank-details h3 {
            color: #2563eb;
            margin-bottom: 15px;
            font-size: 16px;
        }
        .bank-details p {
            margin: 8px 0;
        }
        .bank-details strong {
            display: inline-block;
            width: 120px;
        }
        .footer {
            margin-top: 60px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            color: #666;
            font-size: 11px;
        }
        .status {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 4px;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 11px;
        }
        .status-paid {
            background-color: #d1fae5;
            color: #065f46;
        }
        .status-sent {
            background-color: #dbeafe;
            color: #1e40af;
        }
        .status-draft {
            background-color: #f3f4f6;
            color: #374151;
        }
        .status-overdue {
            background-color: #fee2e2;
            color: #991b1b;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header clearfix">
            <div class="company-info">
                <h1>RentHub</h1>
                <p>Property Rental Platform</p>
                <p>Your Address Here</p>
                <p>Email: info@renthub.com</p>
                <p>Phone: +40 123 456 789</p>
            </div>
            <div class="invoice-info">
                <h2>INVOICE</h2>
                <p class="invoice-number">#{{ $invoice->invoice_number }}</p>
                <p><strong>Date:</strong> {{ $invoice->invoice_date->format('d M Y') }}</p>
                <p><strong>Due Date:</strong> {{ $invoice->due_date->format('d M Y') }}</p>
                <p>
                    <span class="status status-{{ $invoice->status }}">
                        {{ strtoupper($invoice->status) }}
                    </span>
                </p>
            </div>
        </div>

        <!-- Bill To & Property -->
        <div class="parties clearfix">
            <div class="party">
                <h3>Bill To</h3>
                <p><strong>{{ $invoice->customer_name }}</strong></p>
                <p>{{ $invoice->customer_email }}</p>
                @if($invoice->customer_phone)
                    <p>{{ $invoice->customer_phone }}</p>
                @endif
                @if($invoice->customer_address)
                    <p>{{ $invoice->customer_address }}</p>
                @endif
            </div>
            <div class="party">
                <h3>Property Details</h3>
                <p><strong>{{ $invoice->property_title }}</strong></p>
                @if($invoice->property_address)
                    <p>{{ $invoice->property_address }}</p>
                @endif
                @if($invoice->booking)
                    <p><strong>Check-in:</strong> {{ $invoice->booking->check_in_date->format('d M Y') }}</p>
                    <p><strong>Check-out:</strong> {{ $invoice->booking->check_out_date->format('d M Y') }}</p>
                    <p><strong>Guests:</strong> {{ $invoice->booking->number_of_guests }}</p>
                @endif
            </div>
        </div>

        <!-- Invoice Items -->
        <div class="details">
            <table>
                <thead>
                    <tr>
                        <th>Description</th>
                        <th class="text-right">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Rental Fee</td>
                        <td class="text-right">{{ number_format($invoice->subtotal, 2) }} {{ $invoice->currency }}</td>
                    </tr>
                    @if($invoice->cleaning_fee > 0)
                    <tr>
                        <td>Cleaning Fee</td>
                        <td class="text-right">{{ number_format($invoice->cleaning_fee, 2) }} {{ $invoice->currency }}</td>
                    </tr>
                    @endif
                    @if($invoice->security_deposit > 0)
                    <tr>
                        <td>Security Deposit</td>
                        <td class="text-right">{{ number_format($invoice->security_deposit, 2) }} {{ $invoice->currency }}</td>
                    </tr>
                    @endif
                    @if($invoice->taxes > 0)
                    <tr>
                        <td>Taxes & Fees</td>
                        <td class="text-right">{{ number_format($invoice->taxes, 2) }} {{ $invoice->currency }}</td>
                    </tr>
                    @endif
                </tbody>
            </table>

            <div class="totals">
                <table>
                    <tr class="total-row">
                        <td>TOTAL AMOUNT</td>
                        <td class="text-right">{{ number_format($invoice->total_amount, 2) }} {{ $invoice->currency }}</td>
                    </tr>
                </table>
            </div>
            <div class="clearfix"></div>
        </div>

        <!-- Bank Details -->
        @if($invoice->bankAccount)
        <div class="bank-details">
            <h3>Payment Details</h3>
            <p><strong>Account Name:</strong> {{ $invoice->bankAccount->account_name }}</p>
            <p><strong>Account Holder:</strong> {{ $invoice->bankAccount->account_holder_name }}</p>
            <p><strong>IBAN:</strong> {{ $invoice->bankAccount->formatted_iban }}</p>
            <p><strong>BIC/SWIFT:</strong> {{ $invoice->bankAccount->bic_swift }}</p>
            <p><strong>Bank Name:</strong> {{ $invoice->bankAccount->bank_name }}</p>
            @if($invoice->bankAccount->bank_address)
                <p><strong>Bank Address:</strong> {{ $invoice->bankAccount->bank_address }}</p>
            @endif
            <p style="margin-top: 15px; color: #991b1b;">
                <strong>Reference:</strong> Please include invoice number {{ $invoice->invoice_number }} in your payment reference.
            </p>
        </div>
        @endif

        @if($invoice->notes)
        <div style="margin-top: 30px; padding: 15px; background-color: #fffbeb; border-left: 4px solid #f59e0b;">
            <h3 style="color: #92400e; margin-bottom: 10px; font-size: 14px;">Notes</h3>
            <p style="color: #78350f;">{{ $invoice->notes }}</p>
        </div>
        @endif

        <!-- Footer -->
        <div class="footer">
            <p>Thank you for your business!</p>
            <p>This is an automatically generated invoice.</p>
            <p>Generated on {{ now()->format('d M Y H:i') }}</p>
        </div>
    </div>
</body>
</html>
