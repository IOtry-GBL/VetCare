<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirm Adherence Test</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { margin: 0; min-height: 100vh; display: grid; place-items: center; padding: 1rem; background: linear-gradient(135deg, #edf3f6, #f9f4ee); color: #17313d; font-family: "Aptos", "Segoe UI", sans-serif; }
        main { width: min(100%, 520px); padding: 2rem; border: 1px solid rgba(23,49,61,.12); border-radius: 1.5rem; background: rgba(255,255,255,.92); box-shadow: 0 24px 60px rgba(23,49,61,.12); }
        h1 { margin-top: 0; }
        p { color: #5f6f7d; }
        button { min-height: 48px; padding: .75rem 1.2rem; border: 0; border-radius: .8rem; background: linear-gradient(135deg, #0f8b8d, #0d6475); color: #fff; font-weight: 700; cursor: pointer; }
        .status { padding: .85rem; border-radius: .8rem; background: #e8f5ee; color: #176b45; }
    </style>
</head>
<body>
    <main>
        <h1>Adherence Notification Test</h1>
        <p>This is a test confirmation for <strong>{{ $test->recipient_name }}</strong> at {{ $test->recipient_email }}.</p>
        <p>Test medication: <strong>{{ $test->medication_name }}</strong> — {{ $test->dosage }}.</p>

        @if ($test->status === 'Sent' && $confirmationUrl)
            <p>This action only updates the test result. It does not sign you in or change any real medication record.</p>
            <form method="POST" action="{{ $confirmationUrl }}">
                @csrf
                <button type="submit">Confirm Test Notification</button>
            </form>
        @elseif ($test->status === 'Confirmed')
            <p class="status" role="status">Test notification confirmed successfully at {{ $test->confirmed_at?->format('M j, Y g:i A') }}.</p>
        @elseif ($test->status === 'Expired')
            <p class="status" role="status">This test confirmation link has expired. Ask the clinic to send another test notification.</p>
        @else
            <p class="status" role="status">This test notification is {{ strtolower($test->status) }} and cannot be confirmed.</p>
        @endif
    </main>
</body>
</html>
