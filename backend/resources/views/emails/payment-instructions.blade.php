<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instrucțiuni de Plată - RentHub</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            background: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #ffffff;
            padding: 30px 20px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: bold;
        }
        .content {
            padding: 30px 20px;
        }
        .booking-info {
            background: #f8f9fa;
            border-left: 4px solid #667eea;
            padding: 15px;
            margin: 20px 0;
        }
        .booking-info h3 {
            margin: 0 0 10px 0;
            color: #667eea;
            font-size: 18px;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #e9ecef;
        }
        .info-row:last-child {
            border-bottom: none;
        }
        .info-label {
            font-weight: 600;
            color: #6c757d;
        }
        .info-value {
            color: #212529;
            text-align: right;
        }
        .bank-details {
            background: #fff3cd;
            border: 2px solid #ffc107;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .bank-details h3 {
            margin: 0 0 15px 0;
            color: #856404;
            font-size: 18px;
        }
        .bank-row {
            padding: 8px 0;
            display: flex;
            justify-content: space-between;
        }
        .bank-label {
            font-weight: 600;
            color: #856404;
        }
        .bank-value {
            font-family: 'Courier New', monospace;
            background: #ffffff;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 14px;
        }
        .amount-highlight {
            background: #d4edda;
            border: 2px solid #28a745;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            text-align: center;
        }
        .amount-highlight h3 {
            margin: 0 0 10px 0;
            color: #155724;
            font-size: 16px;
        }
        .amount-highlight .amount {
            font-size: 36px;
            font-weight: bold;
            color: #28a745;
        }
        .instructions {
            background: #e7f3ff;
            border-left: 4px solid #0056b3;
            padding: 15px;
            margin: 20px 0;
        }
        .instructions ol {
            margin: 10px 0;
            padding-left: 20px;
        }
        .instructions li {
            padding: 5px 0;
        }
        .warning {
            background: #f8d7da;
            border-left: 4px solid #dc3545;
            padding: 15px;
            margin: 20px 0;
            color: #721c24;
        }
        .footer {
            background: #f8f9fa;
            padding: 20px;
            text-align: center;
            color: #6c757d;
            font-size: 14px;
        }
        .button {
            display: inline-block;
            background: #667eea;
            color: #ffffff;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
            font-weight: bold;
        }
        @media only screen and (max-width: 600px) {
            .container {
                margin: 0;
                border-radius: 0;
            }
            .info-row, .bank-row {
                flex-direction: column;
            }
            .info-value, .bank-value {
                text-align: left;
                margin-top: 5px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>💰 Instrucțiuni de Plată</h1>
            <p style="margin: 10px 0 0 0;">Rezervarea ta așteaptă confirmarea plății</p>
        </div>

        <div class="content">
            <p>Bună {{ $booking->user->name }},</p>
            <p>Mulțumim pentru rezervarea ta la <strong>{{ $booking->property->title }}</strong>!</p>

            <div class="booking-info">
                <h3>📋 Detalii Rezervare</h3>
                <div class="info-row">
                    <span class="info-label">Număr Rezervare:</span>
                    <span class="info-value">{{ $booking->booking_number }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Check-in:</span>
                    <span class="info-value">{{ $booking->check_in->format('d.m.Y') }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Check-out:</span>
                    <span class="info-value">{{ $booking->check_out->format('d.m.Y') }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Număr nopți:</span>
                    <span class="info-value">{{ $booking->nights }} {{ $booking->nights == 1 ? 'noapte' : 'nopți' }}</span>
                </div>
            </div>

            <div class="amount-highlight">
                <h3>Suma de plată</h3>
                <div class="amount">{{ number_format($payment->amount, 2, ',', '.') }} {{ $payment->currency }}</div>
                <p style="margin: 10px 0 0 0; color: #155724; font-size: 14px;">
                    Număr Plată: <strong>{{ $payment->payment_number }}</strong>
                </p>
            </div>

            <div class="bank-details">
                <h3>🏦 Detalii Cont Bancar</h3>
                <div class="bank-row">
                    <span class="bank-label">Bancă:</span>
                    <span class="bank-value">{{ $bankAccount->bank_name }}</span>
                </div>
                <div class="bank-row">
                    <span class="bank-label">Beneficiar:</span>
                    <span class="bank-value">{{ $bankAccount->account_holder }}</span>
                </div>
                @if($bankAccount->iban)
                <div class="bank-row">
                    <span class="bank-label">IBAN:</span>
                    <span class="bank-value">{{ $bankAccount->iban }}</span>
                </div>
                @endif
                <div class="bank-row">
                    <span class="bank-label">Cont:</span>
                    <span class="bank-value">{{ $bankAccount->account_number }}</span>
                </div>
                @if($bankAccount->swift_code)
                <div class="bank-row">
                    <span class="bank-label">SWIFT/BIC:</span>
                    <span class="bank-value">{{ $bankAccount->swift_code }}</span>
                </div>
                @endif
            </div>

            <div class="instructions">
                <h3 style="margin: 0 0 10px 0; color: #0056b3;">📝 Pași de urmat:</h3>
                <ol>
                    <li>Efectuează transferul bancar folosind detaliile de mai sus</li>
                    <li><strong>IMPORTANT:</strong> Menționează numărul de plată <code>{{ $payment->payment_number }}</code> în detaliile transferului</li>
                    <li>După ce transferul este finalizat, accesează contul tău RentHub</li>
                    <li>Încarcă dovada plății (PDF/screenshot) în secțiunea de plăți</li>
                    <li>Proprietarul va verifica plata și rezervarea ta va fi confirmată</li>
                </ol>
            </div>

            <div class="warning">
                <strong>⚠️ Atenție:</strong> Rezervarea ta va fi confirmată după ce proprietarul va verifica plata. Te rugăm să încarci dovada plății cât mai curând posibil pentru a evita anularea rezervării.
            </div>

            <div style="text-align: center;">
                <a href="{{ config('app.frontend_url') }}/dashboard/bookings/{{ $booking->id }}" class="button">
                    Vezi Rezervarea
                </a>
            </div>

            <p style="margin-top: 30px; color: #6c757d; font-size: 14px;">
                Dacă ai întrebări, nu ezita să contactezi proprietarul prin sistemul de mesaje RentHub.
            </p>
        </div>

        <div class="footer">
            <p style="margin: 0;">© {{ date('Y') }} RentHub. Toate drepturile rezervate.</p>
            <p style="margin: 10px 0 0 0;">
                <a href="{{ config('app.frontend_url') }}" style="color: #667eea; text-decoration: none;">RentHub.com</a>
            </p>
        </div>
    </div>
</body>
</html>
