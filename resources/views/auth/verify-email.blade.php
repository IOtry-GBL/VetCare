@extends('layouts.app')

@section('title', 'Verify Email')

@section('content')
    <section class="card compact-shell" style="max-width: 42rem; margin: 4rem auto;">
        <div class="surface-header">
            <div>
                <h1>Verify your email address</h1>
                <p class="section-copy">
                    We sent a verification link to <strong>{{ auth()->user()->email }}</strong>.
                    Confirm your email before accessing your pet-owner account.
                </p>
            </div>
        </div>

        @if (session('status') === 'verification-link-sent')
            <div class="alert alert-info">A new verification link has been sent.</div>
        @endif

        <div class="action-row">
            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <button type="submit" class="btn btn-primary">Resend Verification Email</button>
            </form>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn btn-secondary">Log Out</button>
            </form>
        </div>
    </section>
@endsection
