<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice {{ $invoice->invoice_number }}</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background-color: #2563eb; color: white; padding: 30px; text-align: center; border-radius: 8px 8px 0 0;">
        <h1 style="margin: 0; font-size: 28px;">RentHub</h1>
        <p style="margin: 10px 0 0 0; font-size: 16px;">Invoice #{{ $invoice->invoice_number }}</p>
    </div>

    <div style="background-color: #ffffff; padding: 30px; border: 1px solid #e5e7eb; border-top: none; border-radius: 0 0 8px 8px;">
        <p style="font-size: 16px; margin-bottom: 20px;">Dear <strong>{{ $invoice->customer_name }}</strong>,</p>

        <p>Thank you for your booking with RentHub. Please find your invoice attached to this email.</p>

        <div style="background-color: #f9fafb; padding: 20px; border-radius: 8px; margin: 25px 0;">
            <h2 style="color: #2563eb; margin-top: 0; font-size: 18px;">Invoice Summary</h2>
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="padding: 8px 0;"><strong>Invoice Number:</strong></td>
                    <td style="padding: 8px 0; text-align: right;">{{ $invoice->invoice_number }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0;"><strong>Invoice Date:</strong></td>
                    <td style="padding: 8px 0; text-align: right;">{{ $invoice->invoice_date->format('d M Y') }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0;"><strong>Due Date:</strong></td>
                    <td style="padding: 8px 0; text-align: right;">{{ $invoice->due_date->format('d M Y') }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0;"><strong>Property:</strong></td>
                    <td style="padding: 8px 0; text-align: right;">{{ $invoice->property_title }}</td>
                </tr>
                <tr style="border-top: 2px solid #2563eb;">
                    <td style="padding: 12px 0; font-size: 18px;"><strong>Total Amount:</strong></td>
                    <td style="padding: 12px 0; text-align: right; font-size: 18px; color: #2563eb;"><strong>{{ number_format($invoice->total_amount, 2) }} {{ $invoice->currency }}</strong></td>
                </tr>
            </table>
        </div>

        @if($invoice->bankAccount)
        <div style="background-color: #eff6ff; padding: 20px; border-radius: 8px; border-left: 4px solid #2563eb; margin: 25px 0;">
            <h3 style="color: #1e40af; margin-top: 0; font-size: 16px;">Payment Instructions</h3>
            <p style="margin: 10px 0;"><strong>Account Name:</strong> {{ $invoice->bankAccount->account_name }}</p>
            <p style="margin: 10px 0;"><strong>IBAN:</strong> {{ $invoice->bankAccount->formatted_iban }}</p>
            <p style="margin: 10px 0;"><strong>BIC/SWIFT:</strong> {{ $invoice->bankAccount->bic_swift }}</p>
            <p style="margin: 10px 0;"><strong>Bank:</strong> {{ $invoice->bankAccount->bank_name }}</p>
            <p style="margin: 15px 0 10px 0; padding: 12px; background-color: #fef3c7; border-radius: 6px; color: #78350f;">
                <strong>Important:</strong> Please include <strong>{{ $invoice->invoice_number }}</strong> as the payment reference.
            </p>
        </div>
        @endif

        @if($invoice->booking)
        <div style="margin: 25px 0;">
            <h3 style="color: #2563eb; font-size: 16px;">Booking Details</h3>
            <p style="margin: 8px 0;"><strong>Check-in:</strong> {{ $invoice->booking->check_in_date->format('d M Y') }}</p>
            <p style="margin: 8px 0;"><strong>Check-out:</strong> {{ $invoice->booking->check_out_date->format('d M Y') }}</p>
            <p style="margin: 8px 0;"><strong>Guests:</strong> {{ $invoice->booking->number_of_guests }}</p>
            <p style="margin: 8px 0;"><strong>Nights:</strong> {{ $invoice->booking->number_of_nights }}</p>
        </div>
        @endif

        <div style="margin: 30px 0; padding: 15px; background-color: #f0fdf4; border-radius: 8px; border-left: 4px solid #10b981;">
            <p style="margin: 0; color: #065f46;">
                <strong>📄 Invoice Attached:</strong> Your detailed invoice is attached as a PDF to this email.
            </p>
        </div>

        @if($invoice->notes)
        <div style="margin: 20px 0; padding: 15px; background-color: #fffbeb; border-radius: 8px; border-left: 4px solid #f59e0b;">
            <h4 style="color: #92400e; margin-top: 0; font-size: 14px;">Additional Notes</h4>
            <p style="margin: 0; color: #78350f;">{{ $invoice->notes }}</p>
        </div>
        @endif

        <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #e5e7eb;">
            <p>If you have any questions about this invoice, please don't hesitate to contact us.</p>
            <p>Thank you for choosing RentHub!</p>
            <p style="margin-top: 20px;">
                <strong>Best regards,</strong><br>
                The RentHub Team
            </p>
        </div>
    </div>

    <div style="text-align: center; padding: 20px; color: #6b7280; font-size: 12px;">
        <p>This is an automated email. Please do not reply to this message.</p>
        <p>© {{ date('Y') }} RentHub. All rights reserved.</p>
        <p style="margin-top: 10px;">
            <a href="mailto:info@renthub.com" style="color: #2563eb; text-decoration: none;">info@renthub.com</a> | 
            <a href="tel:+40123456789" style="color: #2563eb; text-decoration: none;">+40 123 456 789</a>
        </p>
    </div>
</body>
</html>
