<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Adherence Notification Test</title>
</head>
<body style="margin: 0; padding: 24px; color: #17313d; font-family: Arial, sans-serif; line-height: 1.5;">
    <main style="max-width: 560px; margin: 0 auto; padding: 24px; border: 1px solid #dce5e8; border-radius: 16px;">
        <h1 style="font-size: 22px;">Medication Reminder Test</h1>
        <p>Hello {{ $recipientName }},</p>
        <p>This is a test notification from your veterinary clinic. No real medication record will be changed.</p>
        <p><strong>{{ $medicationName }}</strong><br>{{ $dosage }}</p>
        <p>
            <a href="{{ $confirmationUrl }}" style="display: inline-block; padding: 12px 18px; border-radius: 8px; background: #0f8b8d; color: #fff; font-weight: bold; text-decoration: none;">
                Review Test Confirmation
            </a>
        </p>
        <p>The link expires at {{ $expiresAt->format('M j, Y g:i A') }}. It does not sign you in or confirm any real dose. On the confirmation page, press the button to record this test.</p>
    </main>
</body>
</html>
