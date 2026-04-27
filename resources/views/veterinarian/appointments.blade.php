@extends('layouts.app')

@section('title', 'Appointments')

@section('sidebar')
    <a href="{{ route('vet.dashboard') }}" class="sidebar-item">Dashboard</a>
    <a href="{{ route('vet.appointments') }}" class="sidebar-item active">Appointments</a>

    <a href="{{ route('vet.medical-records') }}" class="sidebar-item">Medical Records</a>
    <a href="{{ route('vet.prescriptions') }}" class="sidebar-item">E-Prescriptions</a>
    <a href="{{ route('vet.adherence-monitoring') }}" class="sidebar-item">Medication Adherence</a>
@endsection

@section('content')
    <section class="hero-card">
        <div class="hero-row">
            <div>
                <h1 class="hero-title">Appointments</h1>
                <p class="hero-copy">
                    Review your pending requests, start approved appointment sessions in a separate tab, and mark visits as missed when the pet owner does not arrive.
                </p>
            </div>

            <div class="action-row">
                <a href="{{ route('vet.appointments.create') }}" class="btn btn-primary">Create Appointment</a>
            </div>
        </div>
    </section>

    @if (session('success'))
        <div class="alert alert-info">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="banner banner-danger">
            {{ session('error') }}
        </div>
    @endif

    <section class="card">
        <div class="surface-header">
            <div>
                <h2>Pending Appointments</h2>
                <p class="section-copy">Assigned requests that are still waiting on clinic approval.</p>
            </div>
            <span class="count-chip">{{ $pendingAppointments->count() }}</span>
        </div>

        <div class="list-grid">
            @forelse ($pendingAppointments as $appointment)
                <article class="list-card">
                    <div>
                        <div class="item-title">{{ $appointment->pet->name }} - {{ $appointment->pet->owner->name ?? 'Unknown Owner' }}</div>
                        <div class="item-meta">
                            <span class="pill pill-neutral">{{ $appointment->appointment_date?->format('M d, Y g:i A') ?? 'No time set' }}</span>
                            <span class="pill pill-warning">{{ $appointment->status }}</span>
                        </div>

                        @if ($appointment->reason)
                            <p class="item-copy" style="margin-top: 0.8rem;">Reason: {{ $appointment->reason }}</p>
                        @endif
                    </div>

                    <div class="action-row" style="justify-content: flex-end;">
                        <span class="status-badge status-pending">Pending</span>
                    </div>
                </article>
            @empty
                <div class="empty-state">
                    No pending appointments are currently assigned to you.
                </div>
            @endforelse
        </div>
    </section>

    <section class="card">
        <div class="surface-header">
            <div>
                <h2>Approved Appointments</h2>
                <p class="section-copy">Open the live session workspace, save the medical record, and issue medication when needed.</p>
            </div>
            <span class="count-chip">{{ $approvedAppointments->count() }}</span>
        </div>

        <div class="list-grid">
            @forelse ($approvedAppointments as $appointment)
                @php
                <article class="list-card">
                    <div>
                        <div class="item-title">{{ $appointment->pet->name }} - {{ $appointment->pet->owner->name ?? 'Unknown Owner' }}</div>
                        <div class="item-meta">
                            <span class="pill pill-neutral">{{ $appointment->appointment_date?->format('M d, Y g:i A') ?? 'No time set' }}</span>
                            <span class="pill pill-neutral">{{ $appointment->status }}</span>
                        </div>

                        @if ($appointment->reason)
                            <p class="item-copy" style="margin-top: 0.8rem;">Reason: {{ $appointment->reason }}</p>
                        @endif
                    </div>

                    <div class="stack" style="min-width: 220px;">
                        <span class="status-badge status-approved">Approved</span>

                        <a href="{{ route('vet.appointments.session', $appointment) }}" class="btn btn-primary" target="_blank" rel="noopener">
                            Start Session
                        </a>

                        <form method="POST" action="{{ route('vet.appointments.dna', $appointment) }}">
                            @csrf
                            <button
                                type="submit"
                                class="btn btn-danger"
                                style="width: 100%;"
                                onclick="return confirm('Mark this appointment as Did Not Arrive? The pet owner will see it as missed.')">
                                Did Not Arrive
                            </button>
                        </form>
                    </div>
                </article>
            @empty
                <div class="empty-state">
                    No approved appointments are ready for session work right now.
                </div>
            @endforelse
        </div>
    </section>
@endsection
