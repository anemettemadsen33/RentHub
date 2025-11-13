<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cod de Verificare - RentHub</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .container {
            background: white;
            border-radius: 10px;
            padding: 40px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .logo {
            font-size: 32px;
            font-weight: bold;
            color: #3b82f6;
        }
        .code-box {
            background: #f0f9ff;
            border: 2px dashed #3b82f6;
            border-radius: 8px;
            padding: 30px;
            text-align: center;
            margin: 30px 0;
        }
        .code {
            font-size: 42px;
            font-weight: bold;
            letter-spacing: 8px;
            color: #1e40af;
            font-family: 'Courier New', monospace;
        }
        .message {
            color: #666;
            margin-bottom: 20px;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            font-size: 14px;
            color: #6b7280;
            text-align: center;
        }
        .warning {
            background: #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">🏠 RentHub</div>
        </div>

        <h2 style="color: #1e40af; margin-bottom: 20px;">Verifică-ți Adresa de Email</h2>

        <p class="message">
            @if($userName)
                Bună, {{ $userName }}! 👋
            @else
                Bună! 👋
            @endif
        </p>

        <p class="message">
            Ai solicitat un cod de verificare pentru contul tău RentHub. Introdu codul de mai jos pentru a confirma adresa de email:
        </p>

        <div class="code-box">
            <div style="font-size: 14px; color: #6b7280; margin-bottom: 10px;">Codul tău de verificare este:</div>
            <div class="code">{{ $code }}</div>
        </div>

        <div class="warning">
            <strong>⏰ Atenție:</strong> Acest cod este valabil {{ $expiresInMinutes }} minute și poate fi folosit o singură dată.
        </div>

        <p class="message">
            Dacă nu ai solicitat acest cod, te rugăm să ignori acest email. Contul tău este în siguranță.
        </p>

        <div class="footer">
            <p>
                © {{ date('Y') }} RentHub. Toate drepturile rezervate.
            </p>
            <p style="margin-top: 10px;">
                <a href="{{ config('app.url') }}" style="color: #3b82f6; text-decoration: none;">Vizitează RentHub</a>
            </p>
        </div>
    </div>
</body>
</html>
