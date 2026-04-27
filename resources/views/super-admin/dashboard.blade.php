@extends('layouts.app')

@section('sidebar')
    @include('super-admin.sidebar')
@endsection

@section('content')
    @if (session('success'))
        <div class="alert alert-info">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="alert" style="margin-bottom: 1rem; border: 1px solid #fecaca; background: #fef2f2; color: #991b1b;">
            {{ session('error') }}
        </div>
    @endif

    <div class="card">
        <div style="display: flex; justify-content: space-between; gap: 1rem; align-items: start; flex-wrap: wrap;">
            <div>
                <h1 style="margin: 0;">Super Admin Dashboard</h1>
                <p style="margin: 0.5rem 0 0 0; color: #6b7280;">
                    System-wide oversight, role switching, and platform health at a glance.
                </p>
            </div>

            <div style="padding: 0.65rem 0.9rem; border-radius: 999px; background: #ede9fe; color: #5b21b6; font-weight: 700;">
                Current View: {{ $currentRole }}
            </div>
        </div>
    </div>

    <div class="grid" style="grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));">
        <div class="widget">
            <div class="widget-title">Total Users</div>
            <div class="widget-value">{{ $stats['total_users'] }}</div>
            <p style="color: #6b7280; margin-top: 0.5rem;">Across all roles</p>
        </div>
        <div class="widget">
            <div class="widget-title">Pet Owners</div>
            <div class="widget-value">{{ $stats['owners'] }}</div>
            <p style="color: #6b7280; margin-top: 0.5rem;">Client accounts</p>
        </div>
        <div class="widget">
            <div class="widget-title">Veterinarians</div>
            <div class="widget-value">{{ $stats['vets'] }}</div>
            <p style="color: #6b7280; margin-top: 0.5rem;">Clinical accounts</p>
        </div>
        <div class="widget">
            <div class="widget-title">Staff</div>
            <div class="widget-value">{{ $stats['staff'] }}</div>
            <p style="color: #6b7280; margin-top: 0.5rem;">Operations accounts</p>
        </div>
        <div class="widget">
            <div class="widget-title">Super Admins</div>
            <div class="widget-value">{{ $stats['admins'] }}</div>
            <p style="color: #6b7280; margin-top: 0.5rem;">Elevated access</p>
        </div>
        <div class="widget">
            <div class="widget-title">Appointments</div>
            <div class="widget-value">{{ $stats['total_appointments'] }}</div>
            <p style="color: #6b7280; margin-top: 0.5rem;">Recorded in system</p>
        </div>
    </div>

    <div class="card">
        <h2 style="margin-top: 0;">Role Switching</h2>
        <p style="margin: 0.5rem 0 1rem 0; color: #6b7280;">
            Open the app from another role's point of view without leaving your super admin account.
        </p>

        <div style="display: flex; gap: 1rem; flex-wrap: wrap; align-items: end;">
            <form action="{{ route('super-admin.switch-role') }}" method="POST" style="display: flex; gap: 0.75rem; flex-wrap: wrap; align-items: end;">
                @csrf
                <div>
                    <label for="role" style="display: block; margin-bottom: 0.4rem; font-weight: 600;">Switch To</label>
                    <select id="role" name="role" class="form-control" style="min-width: 220px;" required>
                        <option value="owner">Pet Owner</option>
                        <option value="vet">Veterinarian</option>
                        <option value="staff">Staff</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Open Role View</button>
            </form>

            <form action="{{ route('super-admin.reset') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-secondary">Reset To Super Admin</button>
            </form>
        </div>
    </div>

    <div class="card">
        <h2 style="margin-top: 0;">Platform Totals</h2>
        <div style="display: grid; gap: 0.85rem;">
            <div style="display: flex; justify-content: space-between; gap: 1rem; padding: 0.9rem 1rem; border: 1px solid #e5e7eb; border-radius: 0.85rem; background: #f8fafc;">
                <span>Total Pets</span>
                <strong>{{ $stats['total_pets'] }}</strong>
            </div>
            <div style="display: flex; justify-content: space-between; gap: 1rem; padding: 0.9rem 1rem; border: 1px solid #e5e7eb; border-radius: 0.85rem; background: #f8fafc;">
                <span>Appointments Logged</span>
                <strong>{{ $stats['total_appointments'] }}</strong>
            </div>
            <div style="display: flex; justify-content: space-between; gap: 1rem; padding: 0.9rem 1rem; border: 1px solid #e5e7eb; border-radius: 0.85rem; background: #f8fafc;">
                <span>Users With Elevated Access</span>
                <strong>{{ $stats['admins'] }}</strong>
            </div>
        </div>
    </div>
@endsection
