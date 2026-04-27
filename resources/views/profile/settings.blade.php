@extends('layouts.app')

@section('title', 'Profile Settings')

@section('sidebar')
    @if (auth()->user()->hasRole('vet'))
        <a href="{{ route('vet.dashboard') }}" class="sidebar-item">Dashboard</a>
    @elseif (auth()->user()->hasRole('owner'))
        <a href="{{ route('pet-owner.dashboard') }}" class="sidebar-item">Dashboard</a>
    @elseif (auth()->user()->hasRole('staff'))
        <a href="{{ route('staff.dashboard') }}" class="sidebar-item">Dashboard</a>
    @elseif (auth()->user()->hasRole('admin'))
        <a href="{{ route('admin.dashboard') }}" class="sidebar-item">Dashboard</a>
    @endif

    <a href="{{ route('profile.settings') }}" class="sidebar-item active">Profile Settings</a>
@endsection

@section('content')
    <section class="hero-card">
        <div class="hero-row">
            <div>
                <h1 class="hero-title">Profile Settings</h1>
                <p class="hero-copy">
                    Update your account details and keep your clinic contact information current.
                </p>
            </div>
        </div>
    </section>

    @if (session('success'))
        <div class="alert alert-info">
            {{ session('success') }}
        </div>
    @endif

    <section class="card">
        <div class="surface-header">
            <div>
                <h2>Account Details</h2>
                <p class="section-copy">Save your basic profile information and optionally set a new password.</p>
            </div>
            <span class="pill pill-neutral">{{ $user->role }}</span>
        </div>

        <form action="{{ route('profile.settings.update') }}" method="POST" class="stack">
            @csrf
            @method('PUT')

            <div class="form-grid">
                <div class="field">
                    <label class="field-label" for="full_name">Full Name</label>
                    <input id="full_name" name="full_name" type="text" class="field-control" value="{{ old('full_name', $user->full_name) }}" required>
                    @error('full_name')
                        <p class="field-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="field">
                    <label class="field-label" for="email">Email Address</label>
                    <input id="email" name="email" type="email" class="field-control" value="{{ old('email', $user->email) }}" required>
                    @error('email')
                        <p class="field-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="field">
                    <label class="field-label" for="contact_no">Phone Number</label>
                    <input id="contact_no" name="contact_no" type="text" class="field-control" value="{{ old('contact_no', $user->contact_no) }}">
                    @error('contact_no')
                        <p class="field-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="field">
                    <label class="field-label">Role</label>
                    <div class="field-control" style="display: flex; align-items: center; background: rgba(23, 49, 61, 0.04);">
                        {{ $user->role }}
                    </div>
                </div>
            </div>

            <div class="form-grid">
                <div class="field">
                    <label class="field-label" for="password">New Password</label>
                    <input id="password" name="password" type="password" class="field-control" placeholder="Leave blank to keep your current password">
                    @error('password')
                        <p class="field-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="field">
                    <label class="field-label" for="password_confirmation">Confirm New Password</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" class="field-control" placeholder="Repeat the new password">
                </div>
            </div>

            <div class="action-row">
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </div>
        </form>
    </section>
@endsection
