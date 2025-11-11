<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Two-Factor Authentication Code</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background-color: #2563eb; color: white; padding: 30px; text-align: center; border-radius: 8px 8px 0 0;">
        <h1 style="margin: 0; font-size: 28px;">🔐 RentHub</h1>
        <p style="margin: 10px 0 0 0; font-size: 16px;">Two-Factor Authentication</p>
    </div>

    <div style="background-color: #ffffff; padding: 30px; border: 1px solid #e5e7eb; border-top: none; border-radius: 0 0 8px 8px;">
        <p style="font-size: 16px; margin-bottom: 20px;">Hello <strong>{{ $user->name }}</strong>,</p>

        <p>You have requested to sign in to your RentHub account. To complete the sign-in process, please use the following verification code:</p>

        <div style="background-color: #f9fafb; padding: 30px; border-radius: 8px; margin: 25px 0; text-align: center;">
            <p style="margin: 0 0 15px 0; font-size: 14px; color: #6b7280; text-transform: uppercase; letter-spacing: 1px;">Your Verification Code</p>
            <div style="font-size: 36px; font-weight: bold; color: #2563eb; letter-spacing: 8px; font-family: 'Courier New', monospace;">
                {{ $code }}
            </div>
            <p style="margin: 15px 0 0 0; font-size: 13px; color: #9ca3af;">
                This code will expire at <strong>{{ $expiresAt }}</strong>
            </p>
        </div>

        <div style="background-color: #fef3c7; padding: 20px; border-radius: 8px; border-left: 4px solid #f59e0b; margin: 25px 0;">
            <p style="margin: 0; color: #78350f; font-size: 14px;">
                <strong>⚠️ Security Notice:</strong> If you did not request this code, please ignore this email and ensure your account is secure. Consider changing your password if you suspect unauthorized access.
            </p>
        </div>

        <div style="margin: 25px 0; padding: 15px; background-color: #eff6ff; border-radius: 8px;">
            <h3 style="color: #1e40af; margin-top: 0; font-size: 16px;">How to Use This Code</h3>
            <ol style="margin: 10px 0; padding-left: 20px; color: #1e3a8a;">
                <li style="margin: 8px 0;">Return to the RentHub login page</li>
                <li style="margin: 8px 0;">Enter the 6-digit code shown above</li>
                <li style="margin: 8px 0;">Click "Verify" to complete sign-in</li>
            </ol>
        </div>

        <p style="margin-top: 30px; color: #6b7280; font-size: 13px;">
            This is an automated message. Please do not reply to this email.
        </p>
    </div>

    <div style="text-align: center; padding: 20px; color: #9ca3af; font-size: 12px;">
        <p style="margin: 5px 0;">&copy; {{ date('Y') }} RentHub. All rights reserved.</p>
        <p style="margin: 5px 0;">Securing your property rental experience.</p>
    </div>
</body>
</html>
