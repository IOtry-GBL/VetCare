@extends('layouts.app')

@section('title', 'Adherence Notification Testing')

@section('sidebar')
    @include('super-admin.sidebar')
@endsection

@section('content')
    <section class="hero-card">
        <div class="hero-row">
            <div>
                <h1 class="hero-title">Adherence Notification Testing</h1>
                <p class="hero-copy">Send a test reminder to an existing pet owner and check the confirmation link.</p>
            </div>
        </div>
    </section>

    @if (session('success'))
        <div class="alert alert-info" role="status">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert banner-danger" role="alert">{{ session('error') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert banner-danger" role="alert">
            <strong>The test could not be sent.</strong>
            <ul style="margin: 0.5rem 0 0; padding-left: 1.25rem;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <section class="card" style="max-width: 800px;">
        <div class="surface-header">
            <div>
                <h2>Send a Test Notification</h2>
                <p class="section-copy">Choose an active pet-owner account to receive a test email. Its one-time link confirms only this test; it does not change medication records.</p>
            </div>
        </div>

        <form action="{{ route('super-admin.adherence-tests.send') }}" method="POST" class="stack">
            @csrf
            <label class="field" for="recipient_user_id">
                <span class="field-label">Pet-owner email account</span>
                <select id="recipient_user_id" name="recipient_user_id" class="field-control" required>
                    <option value="">Select a pet owner</option>
                    @foreach ($recipients as $recipient)
                        <option value="{{ $recipient->user_id }}" @selected((string) old('recipient_user_id') === (string) $recipient->user_id)>
                            {{ $recipient->full_name }} — {{ $recipient->email }}
                        </option>
                    @endforeach
                </select>
            </label>

            @if ($recipients->isEmpty())
                <p class="muted-copy">There are no active pet-owner accounts to test with.</p>
            @endif

            <div class="action-row">
                <button type="submit" class="btn btn-primary" @disabled($recipients->isEmpty())>Send Test Reminder</button>
            </div>
        </form>

        <p class="muted-copy" style="margin-top: 1rem;">The email link never signs anyone in. Opening it displays a test-only confirmation page; the recipient must press Confirm. Links expire after one hour.</p>
    </section>

    <section class="card">
        <div class="surface-header">
            <div>
                <h2>Recent Test Notifications</h2>
                <p class="section-copy">Only isolated test confirmations are recorded here.</p>
            </div>
        </div>

        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Recipient</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th>Sent</th>
                        <th>Expires</th>
                        <th>Confirmed</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($tests as $test)
                        <tr>
                            <td>{{ $test->recipient_name }}</td>
                            <td>{{ $test->recipient_email }}</td>
                            <td>{{ $test->status }}</td>
                            <td>{{ $test->created_at?->format('M j, Y g:i A') }}</td>
                            <td>{{ $test->expires_at?->format('M j, Y g:i A') }}</td>
                            <td>{{ $test->confirmed_at?->format('M j, Y g:i A') ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">No adherence test notifications have been sent.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
