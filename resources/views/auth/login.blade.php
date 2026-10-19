<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - {{ $clinic['name'] }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root {
            --brand-ink: #17313d;
            --brand-teal: #0f8b8d;
            --brand-deep: #0d6475;
            --brand-muted: #687987;
            --brand-line: rgba(23, 49, 61, 0.12);
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 2rem 1.25rem;
            font-family: "Aptos", "Segoe UI", sans-serif;
            background:
                radial-gradient(ellipse at 16% 12%, rgba(15, 139, 141, 0.13), transparent 26rem),
                radial-gradient(ellipse at 88% 78%, rgba(214, 124, 74, 0.12), transparent 24rem),
                linear-gradient(145deg, #eef3f4 0%, #f7f4ef 52%, #edf2f3 100%);
            color: var(--brand-ink);
        }

        .login-layout {
            width: min(100%, 470px);
            display: grid;
            gap: 1.25rem;
        }

        .login-card {
            padding: clamp(1.5rem, 6vw, 2.5rem);
            border: 1px solid rgba(255, 255, 255, 0.82);
            border-radius: 1.75rem;
            background: rgba(255, 255, 255, 0.91);
            box-shadow: 0 28px 70px rgba(23, 49, 61, 0.13), 0 3px 12px rgba(23, 49, 61, 0.04);
            backdrop-filter: blur(16px);
        }

        .login-header {
            text-align: center;
            margin-bottom: 1.75rem;
        }

        .brand-badge {
            display: block;
            width: clamp(5.5rem, 20vw, 7rem);
            aspect-ratio: 1;
            margin: 0 auto 1rem;
            border-radius: 50%;
            object-fit: cover;
            background: #fff;
            padding: 0.3rem;
            border: 1px solid var(--brand-line);
            box-shadow: 0 12px 28px rgba(23, 49, 61, 0.13);
        }

        .login-header h1 {
            max-width: 23rem;
            margin: 0 auto;
            font-size: clamp(1.35rem, 5vw, 1.75rem);
            line-height: 1.18;
            letter-spacing: -0.035em;
            color: var(--brand-ink);
        }

        .login-header p {
            margin: 0.4rem 0 0;
            color: var(--brand-muted);
            font-size: 0.94rem;
            font-weight: 600;
        }

        .welcome-label {
            display: inline-block;
            margin-bottom: 0.7rem;
            color: var(--brand-deep);
            font-size: 0.73rem;
            font-weight: 800;
            letter-spacing: 0.12em;
            text-transform: uppercase;
        }

        .form-group {
            margin-bottom: 1.1rem;
        }

        label {
            display: block;
            margin-bottom: 0.5rem;
            font-size: 0.83rem;
            font-weight: 700;
            color: var(--brand-ink);
        }

        input[type="email"],
        input[type="password"] {
            width: 100%;
            min-height: 52px;
            padding: 0.85rem 1rem;
            border: 1px solid rgba(23, 49, 61, 0.16);
            border-radius: 0.9rem;
            background: #fff;
            color: var(--brand-ink);
            transition: border-color 0.18s ease, box-shadow 0.18s ease;
            font-size: 1rem;
        }

        input[type="email"]:focus,
        input[type="password"]:focus {
            outline: none;
            border-color: rgba(15, 139, 141, 0.7);
            box-shadow: 0 0 0 4px rgba(15, 139, 141, 0.12);
        }

        .password-row {
            display: flex;
            justify-content: flex-end;
            margin-top: 0.55rem;
        }

        .btn {
            width: 100%;
            min-height: 52px;
            border: none;
            border-radius: 0.9rem;
            background: linear-gradient(120deg, var(--brand-teal), var(--brand-deep));
            color: #fff;
            font-weight: 700;
            font-size: 1rem;
            cursor: pointer;
            box-shadow: 0 12px 24px rgba(15, 139, 141, 0.22);
            transition: transform 0.18s ease, box-shadow 0.18s ease, filter 0.18s ease;
        }

        .btn:hover,
        .btn:focus-visible {
            transform: translateY(-2px);
            filter: brightness(1.04);
            box-shadow: 0 16px 28px rgba(15, 139, 141, 0.28);
        }

        .btn:focus-visible,
        a:focus-visible {
            outline: 3px solid rgba(15, 139, 141, 0.45);
            outline-offset: 3px;
        }

        .footer-links {
            display: flex;
            justify-content: center;
            margin-top: 1.15rem;
            font-size: 0.9rem;
        }

        .footer-links a {
            color: var(--brand-deep);
            font-weight: 700;
        }

        .footer-links a:hover {
            text-decoration: underline;
        }

        .clinic-card {
            padding: 1.1rem 1.25rem 1.2rem;
            border: 1px solid rgba(255, 255, 255, 0.9);
            border-radius: 1.6rem;
            background: rgba(255, 255, 255, 0.92);
            box-shadow: 0 16px 36px rgba(23, 49, 61, 0.08);
        }

        .clinic-details {
            display: grid;
            gap: 0.55rem;
        }

        .clinic-detail {
            display: flex;
            align-items: flex-start;
            gap: 0.55rem;
            color: #364a56;
            font-size: 0.8rem;
            line-height: 1.5;
            overflow-wrap: anywhere;
        }

        .clinic-detail strong {
            flex: 0 0 auto;
            color: var(--brand-ink);
        }

        .clinic-about {
            margin-top: 0.8rem;
            padding-top: 0.75rem;
            border-top: 1px solid var(--brand-line);
            color: #53636e;
            font-size: 0.8rem;
            line-height: 1.6;
            text-align: center;
        }

        .error {
            margin-bottom: 1rem;
            padding: 0.85rem 0.9rem;
            border: 1px solid rgba(196, 79, 67, 0.2);
            border-radius: 0.9rem;
            background: rgba(196, 79, 67, 0.08);
            color: #8f322e;
            font-size: 0.9rem;
        }

        .status {
            margin-bottom: 1rem;
            padding: 0.85rem 0.9rem;
            border: 1px solid rgba(25, 135, 84, 0.2);
            border-radius: 0.9rem;
            background: rgba(25, 135, 84, 0.08);
            color: #176b45;
            font-size: 0.9rem;
        }

        .forgot-link {
            color: var(--brand-deep);
            font-size: 0.84rem;
            font-weight: 700;
        }

        .forgot-link:hover {
            text-decoration: underline;
        }

        @media (max-width: 480px) {
            body {
                padding: 1.1rem 0.9rem;
            }

            .login-layout {
                gap: 0.9rem;
            }

            .login-card {
                border-radius: 1.4rem;
            }

            .clinic-card {
                border-radius: 1.25rem;
            }

            .clinic-detail {
                display: block;
            }
        }
    </style>
</head>
<body>
    <main class="login-layout">
        <section class="login-card" aria-labelledby="clinic-name">
            <div class="login-header">
                <img src="{{ asset('images/Test.jpg') }}" alt="Clinic logo" class="brand-badge">
                <span class="welcome-label">Welcome back</span>
                <h1 id="clinic-name">{{ $clinic['name'] }}</h1>
                <p>Sign in to your account</p>
            </div>

            @if (session('status'))
                <div class="status" role="status">{{ session('status') }}</div>
            @endif

            @if ($errors->any())
                <div class="error" role="alert">
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <form action="{{ route('login') }}" method="POST">
                @csrf

                <div class="form-group">
                    <label for="email">Email address</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" autocomplete="username" required autofocus>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" autocomplete="current-password" required>
                    <div class="password-row">
                        <a href="{{ route('password.request') }}" class="forgot-link">Forgot password?</a>
                    </div>
                </div>

                <button type="submit" class="btn">Sign in</button>
            </form>

            <div class="footer-links">
                <a href="{{ route('register') }}">Create an account</a>
            </div>
        </section>

        <section class="clinic-card" aria-label="Clinic information">
            <div class="clinic-details">
                @if ($clinic['phone'])
                    <div class="clinic-detail"><strong>Veterinarian contact:</strong> <span>{{ $clinic['phone'] }}</span></div>
                @endif
                @if ($clinic['address'])
                    <div class="clinic-detail"><strong>Location:</strong> <span>{{ $clinic['address'] }}</span></div>
                @endif
            </div>
            @if ($clinic['description'])
                <p class="clinic-about">{{ $clinic['description'] }}</p>
            @endif
        </section>
    </main>
</body>
</html>
