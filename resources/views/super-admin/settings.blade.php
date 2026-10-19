@extends('layouts.app')

@section('title', 'System Settings')

@section('sidebar')
    @include('super-admin.sidebar')
@endsection

@section('styles')
    <style>
        .settings-page {
            display: grid;
            gap: 1.25rem;
        }

        .settings-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(min(100%, 250px), 1fr));
            gap: 1rem;
        }

        .settings-wide {
            grid-column: 1 / -1;
        }

        .settings-note {
            margin-top: 0.45rem;
            color: var(--shell-muted);
            font-size: 0.86rem;
        }

        .settings-toggle {
            display: flex;
            align-items: center;
            gap: 0.7rem;
            padding: 1rem;
            border: 1px solid var(--shell-line);
            border-radius: var(--shell-radius-md);
            background: rgba(15, 139, 141, 0.055);
        }

        .settings-toggle input {
            width: 1.1rem;
            height: 1.1rem;
            accent-color: var(--shell-accent);
        }

        .settings-intro {
            max-width: 52rem;
        }
    </style>
@endsection

@section('content')
    <div class="settings-page">
        <section class="hero-card">
            <div class="hero-row">
                <div class="settings-intro">
                    <h1 class="hero-title">System Settings</h1>
                    <p class="hero-copy">Manage your clinic profile, platform preferences, and the email service used to contact clients.</p>
                </div>
            </div>
        </section>

        @if (session('success'))
            <div class="alert alert-info" role="status">{{ session('success') }}</div>
        @endif
        @if (session('mail_test_success'))
            <div class="alert alert-info" role="status">{{ session('mail_test_success') }}</div>
        @endif
        @if (session('mail_test_error'))
            <div class="alert alert-danger" role="alert">{{ session('mail_test_error') }}</div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger" role="alert">
                <strong>Please check the highlighted settings.</strong>
                <ul style="margin: 0.5rem 0 0; padding-left: 1.25rem;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <section class="card">
            <div class="surface-header">
                <div>
                    <h2>Clinic &amp; Platform</h2>
                    <p class="section-copy">Update the clinic details visitors see and the core booking preferences.</p>
                </div>
            </div>

            <form action="{{ route('super-admin.settings.update') }}" method="POST" class="stack">
                @csrf
                @method('PUT')
                <div class="settings-grid">
                    <label class="field" for="clinic_name">
                        <span class="field-label">Login page clinic name</span>
                        <input class="field-control" id="clinic_name" name="clinic_name" type="text" value="{{ old('clinic_name', $settings['clinic_name']) }}" required>
                    </label>
                    <label class="field" for="clinic_phone">
                        <span class="field-label">Login page contact number</span>
                        <input class="field-control" id="clinic_phone" name="clinic_phone" type="text" value="{{ old('clinic_phone', $settings['clinic_phone']) }}" maxlength="50">
                    </label>
                    <label class="field settings-wide" for="clinic_address">
                        <span class="field-label">Login page location</span>
                        <textarea class="field-control" id="clinic_address" name="clinic_address" rows="2" maxlength="500">{{ old('clinic_address', $settings['clinic_address']) }}</textarea>
                    </label>
                    <label class="field settings-wide" for="clinic_description">
                        <span class="field-label">Login page clinic description</span>
                        <textarea class="field-control" id="clinic_description" name="clinic_description" rows="3" maxlength="1000">{{ old('clinic_description', $settings['clinic_description']) }}</textarea>
                    </label>
                    <label class="field" for="clinic_email">
                        <span class="field-label">Clinic email</span>
                        <input class="field-control" id="clinic_email" name="clinic_email" type="email" value="{{ old('clinic_email', $settings['clinic_email']) }}" required>
                    </label>
                    <label class="field" for="timezone">
                        <span class="field-label">Timezone</span>
                        <input class="field-control" id="timezone" name="timezone" type="text" value="{{ old('timezone', $settings['timezone']) }}" required>
                    </label>
                    <label class="field" for="appointment_lead_time_hours">
                        <span class="field-label">Appointment lead time (hours)</span>
                        <input class="field-control" id="appointment_lead_time_hours" name="appointment_lead_time_hours" type="number" min="0" max="168" value="{{ old('appointment_lead_time_hours', $settings['appointment_lead_time_hours']) }}" required>
                    </label>
                    <label class="field" for="emergency_contact">
                        <span class="field-label">Emergency contact</span>
                        <input class="field-control" id="emergency_contact" name="emergency_contact" type="text" value="{{ old('emergency_contact', $settings['emergency_contact']) }}">
                    </label>
                </div>
                <label class="settings-toggle">
                    <input type="checkbox" name="online_booking_enabled" value="1" @checked(old('online_booking_enabled', $settings['online_booking_enabled']))>
                    <span><strong>Online booking enabled</strong><br><span class="settings-note">Allow clients to book appointments online.</span></span>
                </label>
                <div class="action-row">
                    <button type="submit" class="btn btn-primary">Save Clinic Settings</button>
                </div>
            </form>
        </section>

        <section class="card">
            <div class="surface-header">
                <div>
                    <h2>Client Email Delivery</h2>
                    <p class="section-copy">Connect your email provider with SMTP so client-facing email notifications are sent from your clinic address.</p>
                </div>
            </div>

            @if ($mailSettings['mail_password_configured'])
                <p class="settings-note" style="margin: 0 0 1rem;">An SMTP password is saved. Leave the password field blank to keep it.</p>
            @else
                <p class="settings-note" style="margin: 0 0 1rem;">Your SMTP password is encrypted when saved and is never shown here.</p>
            @endif
            <div class="alert alert-info" role="note" style="margin-bottom: 1rem;">
                <strong>Gmail setup:</strong> use <code>smtp.gmail.com</code>, port <code>587</code>, and <code>TLS / STARTTLS</code>. Enter the full Gmail address as the username and a Google App Password as the SMTP password; a regular Gmail password will not work.
            </div>

            <form action="{{ route('super-admin.settings.mail.update') }}" method="POST" class="stack">
                @csrf
                <input type="hidden" name="mail_enabled" value="0">
                <label class="settings-toggle">
                    <input type="checkbox" name="mail_enabled" value="1" @checked(old('mail_enabled', $mailSettings['mail_enabled']))>
                    <span><strong>Use custom SMTP for client emails</strong><br><span class="settings-note">When disabled, Laravel uses the server's existing mail configuration.</span></span>
                </label>

                <div class="settings-grid">
                    <label class="field" for="mail_host">
                        <span class="field-label">SMTP host</span>
                        <input class="field-control" id="mail_host" name="mail_host" type="text" value="{{ old('mail_host', $mailSettings['mail_host']) }}" maxlength="255" placeholder="smtp.example.com">
                    </label>
                    <label class="field" for="mail_port">
                        <span class="field-label">SMTP port</span>
                        <input class="field-control" id="mail_port" name="mail_port" type="number" min="1" max="65535" value="{{ old('mail_port', $mailSettings['mail_port']) }}" placeholder="587">
                    </label>
                    <label class="field" for="mail_encryption">
                        <span class="field-label">Connection encryption</span>
                        <select class="field-control" id="mail_encryption" name="mail_encryption" required>
                            <option value="tls" @selected(old('mail_encryption', $mailSettings['mail_encryption']) === 'tls')>TLS / STARTTLS (recommended)</option>
                            <option value="ssl" @selected(old('mail_encryption', $mailSettings['mail_encryption']) === 'ssl')>SSL</option>
                            <option value="none" @selected(old('mail_encryption', $mailSettings['mail_encryption']) === 'none')>None</option>
                        </select>
                    </label>
                    <label class="field" for="mail_username">
                        <span class="field-label">SMTP username</span>
                        <input class="field-control" id="mail_username" name="mail_username" type="text" value="{{ old('mail_username', $mailSettings['mail_username']) }}" maxlength="255" autocomplete="username">
                    </label>
                    <label class="field" for="mail_password">
                        <span class="field-label">SMTP password</span>
                        <input class="field-control" id="mail_password" name="mail_password" type="password" value="" maxlength="4096" autocomplete="new-password" placeholder="{{ $mailSettings['mail_password_configured'] ? 'Saved; leave blank to keep' : 'Enter SMTP password' }}">
                    </label>
                    @if ($mailSettings['mail_password_configured'])
                        <label class="settings-toggle">
                            <input type="checkbox" name="clear_mail_password" value="1" @checked(old('clear_mail_password'))>
                            <span>Clear the saved SMTP password</span>
                        </label>
                    @endif
                    <label class="field" for="mail_from_address">
                        <span class="field-label">Sender email address</span>
                        <input class="field-control" id="mail_from_address" name="mail_from_address" type="email" value="{{ old('mail_from_address', $mailSettings['mail_from_address']) }}" maxlength="255" placeholder="clinic@example.com">
                    </label>
                    <label class="field" for="mail_from_name">
                        <span class="field-label">Sender name</span>
                        <input class="field-control" id="mail_from_name" name="mail_from_name" type="text" value="{{ old('mail_from_name', $mailSettings['mail_from_name']) }}" maxlength="255" placeholder="{{ config('app.name') }}">
                    </label>
                    <label class="field settings-wide" for="test_email">
                        <span class="field-label">Send a test email to</span>
                        <input class="field-control" id="test_email" name="test_email" type="email" value="{{ old('test_email', auth()->user()->email) }}" maxlength="255" placeholder="you@example.com">
                        <span class="settings-note">The test uses the values currently entered above without saving them.</span>
                    </label>
                </div>

                <div class="action-row">
                    <button type="submit" class="btn btn-primary" name="_method" value="PUT">Save Email Settings</button>
                    <button type="submit" class="btn btn-secondary" formaction="{{ route('super-admin.settings.mail.test') }}" formmethod="POST" formnovalidate name="_method" value="POST">Send Test Email</button>
                </div>
            </form>
        </section>
    </div>
@endsection
