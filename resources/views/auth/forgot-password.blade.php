<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - VetCare Clinic</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { margin: 0; min-height: 100vh; display: grid; place-items: center; padding: 1rem; background: linear-gradient(135deg, #edf3f6, #f9f4ee); color: #17313d; font-family: "Aptos", "Segoe UI", sans-serif; }
        .auth-card { width: min(100%, 440px); padding: 2rem; border: 1px solid rgba(23, 49, 61, .12); border-radius: 1.5rem; background: rgba(255,255,255,.9); box-shadow: 0 24px 60px rgba(23,49,61,.12); }
        h1 { margin: 0; font-size: 1.65rem; }
        p { color: #5f6f7d; }
        label { display: block; margin-bottom: .45rem; font-weight: 700; }
        input { width: 100%; min-height: 48px; box-sizing: border-box; padding: .75rem .9rem; border: 1px solid rgba(23,49,61,.2); border-radius: .8rem; }
        button { width: 100%; min-height: 48px; margin-top: 1rem; border: 0; border-radius: .8rem; background: linear-gradient(135deg, #0f8b8d, #0d6475); color: #fff; font-weight: 700; cursor: pointer; }
        .message { margin: 1rem 0; padding: .8rem; border-radius: .7rem; background: #e8f5ee; color: #176b45; }
        .error { margin: 1rem 0; padding: .8rem; border-radius: .7rem; background: #fcebea; color: #8f322e; }
        a { display: block; margin-top: 1rem; color: #0d6475; font-weight: 700; text-align: center; text-decoration: none; }
    </style>
</head>
<body>
    <main class="auth-card">
        <h1>Forgot your password?</h1>
        <p>Enter the email address on your account. If it is registered, we’ll send a password reset link.</p>

        @if (session('status'))
            <div class="message" role="status">{{ session('status') }}</div>
        @endif

        @if (session('mail_error'))
            <div class="error" role="alert">{{ session('mail_error') }}</div>
        @endif

        @if ($errors->any())
            <div class="error" role="alert">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('password.email') }}">
            @csrf
            <label for="email">Email Address</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required autofocus>
            <button type="submit">Send Password Reset Link</button>
        </form>

        <a href="{{ route('login') }}">Back to Login</a>
    </main>
</body>
</html>
