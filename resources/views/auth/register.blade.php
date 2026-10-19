<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - VetCare Clinic</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root {
            --brand-navy: #17313d;
            --brand-teal: #0f8b8d;
            --brand-teal-deep: #0d6475;
            --brand-warm: #d67c4a;
            --brand-surface: rgba(255, 255, 255, 0.82);
            --brand-muted: #5f6f7d;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 1.5rem;
            font-family: "Aptos", "Segoe UI", sans-serif;
            background:
                radial-gradient(circle at top left, rgba(15, 139, 141, 0.16), transparent 22rem),
                radial-gradient(circle at bottom right, rgba(214, 124, 74, 0.16), transparent 18rem),
                linear-gradient(135deg, #edf3f6 0%, #f9f4ee 100%);
            color: var(--brand-navy);
        }

        .register-container {
            width: min(100%, 480px);
            padding: 2rem;
            border: 1px solid rgba(255, 255, 255, 0.68);
            border-radius: 1.75rem;
            background: var(--brand-surface);
            backdrop-filter: blur(14px);
            box-shadow: 0 24px 60px rgba(23, 49, 61, 0.12);
        }

        .register-header {
            text-align: center;
            margin-bottom: 1.8rem;
        }

        .brand-badge {
            display: block;
            width: clamp(5rem, 18vw, 8rem);
            aspect-ratio: 1;
            margin: 0 auto 0.9rem;
            border-radius: 50%;
            object-fit: cover;
            background: rgba(255, 255, 255, 0.7);
            padding: 0.35rem;
            border: 1px solid rgba(23, 49, 61, 0.12);
            box-shadow: 0 12px 24px rgba(23, 49, 61, 0.12);
        }

        .register-header h1 {
            margin: 0;
            font-size: clamp(1.5rem, 2vw, 1.8rem);
            letter-spacing: 0.14em;
            text-transform: uppercase;
            color: var(--brand-navy);
        }

        .register-header p {
            margin: 0.65rem 0 0;
            color: var(--brand-muted);
            font-weight: 600;
        }

        .form-group {
            margin-bottom: 1.25rem;
        }

        label {
            display: block;
            margin-bottom: 0.45rem;
            font-size: 0.88rem;
            font-weight: 700;
            color: var(--brand-navy);
        }

        input[type="text"],
        input[type="email"],
        input[type="password"],
        input[type="tel"] {
            width: 100%;
            min-height: 50px;
            padding: 0.85rem 1rem;
            border: 1px solid rgba(23, 49, 61, 0.16);
            border-radius: 1rem;
            background: rgba(255, 255, 255, 0.95);
            color: var(--brand-navy);
            transition: border-color 0.18s ease, box-shadow 0.18s ease;
            font-size: 1rem;
        }

        input:focus {
            outline: none;
            border-color: rgba(15, 139, 141, 0.7);
            box-shadow: 0 0 0 4px rgba(15, 139, 141, 0.12);
        }

        .btn {
            width: 100%;
            min-height: 50px;
            border: none;
            border-radius: 1rem;
            background: linear-gradient(135deg, var(--brand-teal), var(--brand-teal-deep));
            color: #fff;
            font-weight: 700;
            font-size: 1rem;
            cursor: pointer;
            box-shadow: 0 18px 28px rgba(15, 139, 141, 0.2);
            transition: transform 0.18s ease, box-shadow 0.18s ease;
        }

        .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 20px 30px rgba(15, 139, 141, 0.22);
        }

        .login-link {
            text-align: center;
            margin-top: 1.2rem;
            font-size: 0.92rem;
        }

        .login-link a {
            color: var(--brand-teal-deep);
            font-weight: 700;
            text-decoration: none;
        }

        .login-link a:hover {
            text-decoration: underline;
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

        .password-requirements {
            margin-top: 0.7rem;
            padding: 1rem;
            border: 1px solid rgba(23, 49, 61, 0.12);
            border-radius: 1rem;
            background: rgba(15, 139, 141, 0.04);
            font-size: 0.9rem;
        }

        .requirement {
            display: flex;
            align-items: center;
            margin-bottom: 0.5rem;
            color: #6b7280;
        }

        .requirement:last-child {
            margin-bottom: 0;
        }

        .requirement-icon {
            margin-right: 0.5rem;
            width: 1.25rem;
            text-align: center;
            font-weight: 800;
        }

        .requirement.met {
            color: #0c7b59;
        }

        .requirement.met .requirement-icon {
            color: #10b981;
        }

        .requirement.unmet .requirement-icon {
            color: #ef4444;
        }

        @media (max-width: 480px) {
            body {
                padding: 1rem;
            }

            .register-container {
                padding: 1.3rem;
                border-radius: 1.2rem;
            }
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="register-header">
            <img src="{{ asset('images/Test.jpg') }}" alt="DVCS logo" class="brand-badge">
            <h1>DVCS</h1>
            <p>Create your account</p>
        </div>

        @if ($errors->any())
            <div class="error">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form action="{{ route('register') }}" method="POST">
            @csrf

            <div class="form-group">
                <label for="full_name">Full Name</label>
                <input type="text" id="full_name" name="full_name" value="{{ old('full_name') }}" required autofocus>
            </div>

            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required>
            </div>

            <div class="form-group">
                <label for="contact_no">Phone Number</label>
                <input type="tel" id="contact_no" name="contact_no" value="{{ old('contact_no') }}" required>
            </div>

            <div class="form-group">
                <label for="address">Address</label>
                <textarea id="address" name="address" rows="3">{{ old('address') }}</textarea>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required oninput="validatePassword()">
                
                <div class="password-requirements">
                    <div class="requirement unmet" id="req-length">
                        <span class="requirement-icon">✗</span>
                        <span>At least 8 characters</span>
                    </div>
                    <div class="requirement unmet" id="req-uppercase">
                        <span class="requirement-icon">✗</span>
                        <span>At least one uppercase letter (A-Z)</span>
                    </div>
                    <div class="requirement unmet" id="req-lowercase">
                        <span class="requirement-icon">✗</span>
                        <span>At least one lowercase letter (a-z)</span>
                    </div>

    <script>
        function validatePassword() {
            const password = document.getElementById('password').value;
            
            // Check length (at least 8 characters)
            const lengthReq = document.getElementById('req-length');
            if (password.length >= 8) {
                lengthReq.classList.remove('unmet');
                lengthReq.classList.add('met');
            } else {
                lengthReq.classList.remove('met');
                lengthReq.classList.add('unmet');
            }
            
            // Check uppercase
            const uppercaseReq = document.getElementById('req-uppercase');
            if (/[A-Z]/.test(password)) {
                uppercaseReq.classList.remove('unmet');
                uppercaseReq.classList.add('met');
            } else {
                uppercaseReq.classList.remove('met');
                uppercaseReq.classList.add('unmet');
            }
            
            // Check lowercase
            const lowercaseReq = document.getElementById('req-lowercase');
            if (/[a-z]/.test(password)) {
                lowercaseReq.classList.remove('unmet');
                lowercaseReq.classList.add('met');
            } else {
                lowercaseReq.classList.remove('met');
                lowercaseReq.classList.add('unmet');
            }
            
            // Check number
            const numberReq = document.getElementById('req-number');
            if (/[0-9]/.test(password)) {
                numberReq.classList.remove('unmet');
                numberReq.classList.add('met');
            } else {
                numberReq.classList.remove('met');
                numberReq.classList.add('unmet');
            }
            
            // Check special characters
            const specialReq = document.getElementById('req-special');
            if (/[!@#$%^&*()_+\-=\[\]{};:'",.<>?\/\\|`~]/.test(password)) {
                specialReq.classList.remove('unmet');
                specialReq.classList.add('met');
            } else {
                specialReq.classList.remove('met');
                specialReq.classList.add('unmet');
            }
        }
    </script>
                    <div class="requirement unmet" id="req-number">
                        <span class="requirement-icon">✗</span>
                        <span>At least one number (0-9)</span>
                    </div>
                    <div class="requirement unmet" id="req-special">
                        <span class="requirement-icon">✗</span>
                        <span>At least one special character (!@#$%^&*)</span>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label for="password_confirmation">Confirm Password</label>
                <input type="password" id="password_confirmation" name="password_confirmation" required>
            </div>

            <button type="submit" class="btn">Create Account</button>
        </form>

        <div class="login-link">
            Already have an account? <a href="{{ route('login') }}">Login here</a>
        </div>
    </div>
</body>
</html>
