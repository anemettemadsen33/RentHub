<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bine ai venit - RentHub</title>
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
        .celebration {
            font-size: 64px;
            text-align: center;
            margin: 20px 0;
        }
        .button {
            display: inline-block;
            padding: 14px 32px;
            background: #3b82f6;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            margin: 20px 0;
        }
        .button:hover {
            background: #2563eb;
        }
        .features {
            background: #f9fafb;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .feature-item {
            margin: 15px 0;
            padding-left: 30px;
            position: relative;
        }
        .feature-item:before {
            content: "✓";
            position: absolute;
            left: 0;
            color: #10b981;
            font-weight: bold;
            font-size: 20px;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            font-size: 14px;
            color: #6b7280;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">🏠 RentHub</div>
        </div>

        <div class="celebration">🎉</div>

        <h2 style="color: #1e40af; text-align: center; margin-bottom: 20px;">
            Bine ai venit, {{ $userName }}!
        </h2>

        <p style="text-align: center; font-size: 18px; color: #666;">
            Email-ul tău a fost verificat cu succes!
        </p>

        <p style="margin: 20px 0; color: #666;">
            Ești acum parte din comunitatea RentHub. Poți să:
        </p>

        <div class="features">
            <div class="feature-item">
                Cauți și rezervi proprietăți în câteva clickuri
            </div>
            <div class="feature-item">
                Comunici direct cu proprietarii
            </div>
            <div class="feature-item">
                Înregistrezi proprietăți și primești rezervări
            </div>
            <div class="feature-item">
                Gestionezi rezervările și plățile în siguranță
            </div>
        </div>

        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ config('app.frontend_url') ?? config('app.url') }}" class="button">
                Explorează RentHub
            </a>
        </div>

        <p style="color: #666; margin-top: 30px;">
            Dacă ai întrebări sau ai nevoie de ajutor, echipa noastră de suport este gata să te asiste.
        </p>

        <div class="footer">
            <p>
                © {{ date('Y') }} RentHub. Toate drepturile rezervate.
            </p>
            <p style="margin-top: 10px;">
                <a href="{{ config('app.url') }}" style="color: #3b82f6; text-decoration: none;">Vizitează RentHub</a> |
                <a href="{{ config('app.url') }}/support" style="color: #3b82f6; text-decoration: none;">Suport</a>
            </p>
        </div>
    </div>
</body>
</html>
