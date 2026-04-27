@extends('layouts.app')

@section('sidebar')
    <a href="{{ route('pet-owner.dashboard') }}" class="sidebar-item">Dashboard</a>
    <a href="{{ route('pet-owner.pets') }}" class="sidebar-item">My Pets</a>
    <a href="{{ route('pet-owner.appointments') }}" class="sidebar-item">Appointments</a>

    <a href="{{ route('pet-owner.prescriptions') }}" class="sidebar-item active">Prescriptions</a>
    <a href="{{ route('pet-owner.notifications') }}" class="sidebar-item">Notifications</a>
    <a href="#" class="sidebar-item" onclick="openPetCareAI(event)">Ask Pet Care AI</a>
@endsection

@section('content')
    @php
        $petCount = $prescriptions->pluck('medicalRecord.pet.pet_id')->filter()->unique()->count();
        $latestIssuedAt = $prescriptions->first()?->issued_at;
    @endphp

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
                <h1 style="margin: 0;">Prescriptions</h1>
                <p style="margin: 0.5rem 0 0 0; color: #6b7280;">
                    These are the real prescriptions written from your pet's appointment sessions.
                </p>
            </div>

            <div class="metric-strip">
                <div class="metric-card">
                    <span class="metric-label">Prescriptions</span>
                    <strong>{{ $prescriptions->count() }}</strong>
                </div>
                <div class="metric-card">
                    <span class="metric-label">Pets</span>
                    <strong>{{ $petCount }}</strong>
                </div>
                <div class="metric-card">
                    <span class="metric-label">Latest Issued</span>
                    <strong>{{ $latestIssuedAt ? $latestIssuedAt->format('M j, Y') : 'None yet' }}</strong>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <h2 style="margin-top: 0;">Prescription History</h2>
        <p style="margin: 0.45rem 0 1rem 0; color: #6b7280;">
            Each entry below is pulled from the e-prescriptions created during veterinarian appointment sessions.
        </p>

        <div class="prescription-list">
            @forelse ($prescriptions as $prescription)
                @php
                    $record = $prescription->medicalRecord;
                    $pet = $record?->pet;
                    $veterinarian = $record?->consultation?->veterinarian;
                    $adherenceLogs = $prescription->adherenceLogs->sortBy('scheduled_datetime')->values();
                    $pendingDoseLogs = $adherenceLogs
                        ->filter(fn ($log) => $log->intake_status === 'Pending')
                        ->take(6);
                    $recentDoseLogs = $adherenceLogs
                        ->filter(fn ($log) => in_array($log->intake_status, ['Taken', 'Missed', 'Delayed'], true))
                        ->sortByDesc('scheduled_datetime')
                        ->take(2);
                    $doseLogs = $pendingDoseLogs
                        ->concat($recentDoseLogs)
                        ->unique('adherence_id')
                        ->sortBy('scheduled_datetime')
                        ->values();
                @endphp

                <div class="prescription-card">
                    <div style="display: flex; justify-content: space-between; gap: 1rem; align-items: start; flex-wrap: wrap;">
                        <div>
                            <div class="prescription-title">{{ $prescription->medication_name }}</div>
                            <p style="margin: 0.45rem 0 0 0; color: #6b7280;">
                                Pet: {{ $pet?->name ?? 'Unknown Pet' }}
                                @if ($pet?->species)
                                    ({{ $pet->species }})
                                @endif
                            </p>
                        </div>

                        <span class="issued-chip">
                            {{ $prescription->issued_at?->format('M j, Y g:i A') ?? 'Issued' }}
                        </span>
                    </div>

                    <div class="detail-grid">
                        <div class="detail-panel">
                            <span class="detail-label">Dosage</span>
                            <strong>{{ $prescription->dosage }}</strong>
                        </div>
                        <div class="detail-panel">
                            <span class="detail-label">Frequency</span>
                            <strong>{{ $prescription->frequency }}</strong>
                        </div>
                        <div class="detail-panel">
                            <span class="detail-label">Duration</span>
                            <strong>{{ $prescription->duration }}</strong>
                        </div>
                        <div class="detail-panel">
                            <span class="detail-label">Veterinarian</span>
                            <strong>{{ $veterinarian?->name ? 'Dr. '.$veterinarian->name : 'Assigned veterinarian' }}</strong>
                        </div>
                    </div>

                    <div class="detail-stack">
                        <div>
                            <span class="detail-label">Diagnosis</span>
                            <p>{{ $record?->diagnosis ?: 'No diagnosis note recorded.' }}</p>
                        </div>
                        <div>
                            <span class="detail-label">Treatment Plan</span>
                            <p>{{ $record?->treatment_plan ?: 'No treatment plan recorded.' }}</p>
                        </div>
                        @if ($record?->follow_up_date)
                            <div>
                                <span class="detail-label">Follow-Up Date</span>
                                <p>{{ $record->follow_up_date->format('F j, Y') }}</p>
                            </div>
                        @endif
                    </div>

                    <div class="adherence-panel">
                        <div style="display: flex; justify-content: space-between; gap: 0.75rem; align-items: start; flex-wrap: wrap;">
                            <div>
                                <span class="detail-label">Dose Confirmations</span>
                                <p class="adherence-copy">
                                    Confirm each scheduled dose here even if you missed the reminder notification.
                                </p>
                            </div>

                            @if ($adherenceLogs->isNotEmpty())
                                <a href="{{ route('adherence.prescription-history', $prescription) }}" class="history-link">
                                    View Full History
                                </a>
                            @endif
                        </div>

                        @if ($adherenceLogs->isNotEmpty())
                            <div class="adherence-summary-grid">
                                <div class="adherence-stat">
                                    <span class="detail-label">Taken</span>
                                    <strong>{{ $adherenceLogs->where('intake_status', 'Taken')->count() }}</strong>
                                </div>
                                <div class="adherence-stat">
                                    <span class="detail-label">Pending</span>
                                    <strong>{{ $adherenceLogs->where('intake_status', 'Pending')->count() }}</strong>
                                </div>
                                <div class="adherence-stat">
                                    <span class="detail-label">Missed</span>
                                    <strong>{{ $adherenceLogs->where('intake_status', 'Missed')->count() }}</strong>
                                </div>
                                <div class="adherence-stat">
                                    <span class="detail-label">Delayed</span>
                                    <strong>{{ $adherenceLogs->where('intake_status', 'Delayed')->count() }}</strong>
                                </div>
                            </div>

                            <div class="dose-list">
                                @foreach ($doseLogs as $log)
                                    @php
                                        $scheduledAt = $log->scheduledDatetimeInClinicTimezone();
                                        $deadlineAt = $log->confirmationDeadlineInClinicTimezone();
                                    @endphp

                                    <div class="dose-card">
                                        <div>
                                            <div class="dose-time">
                                                {{ $scheduledAt?->format('M j, Y g:i A') ?? 'Schedule unavailable' }} PH
                                            </div>
                                            <p class="dose-copy">
                                                Confirmation closes {{ $deadlineAt?->format('g:i A') ?? 'N/A' }} PH
                                            </p>
                                        </div>

                                        <div class="dose-action-group">
                                            @if ($log->isAvailableForConfirmation())
                                                <span class="dose-status dose-status-pending">Ready To Confirm</span>
                                                <form action="{{ route('adherence.confirm-dose', $log) }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="btn btn-primary">Confirm Dose</button>
                                                </form>
                                            @elseif ($log->isUpcoming())
                                                <span class="dose-status dose-status-upcoming">Upcoming</span>
                                                <button type="button" class="btn btn-secondary" disabled>
                                                    Available {{ $scheduledAt?->format('g:i A') ?? 'Soon' }}
                                                </button>
                                            @elseif ($log->intake_status === 'Taken')
                                                <span class="dose-status dose-status-confirmed">Confirmed</span>
                                                <span class="dose-note">
                                                    {{ $log->confirmation_time?->timezone(\App\Services\AdherenceService::clinicTimezone())->format('g:i A') ?? 'Recorded' }} PH
                                                </span>
                                            @elseif ($log->intake_status === 'Missed' || $log->isExpiredForConfirmation())
                                                <span class="dose-status dose-status-missed">Window Closed</span>
                                                <span class="dose-note">Dose was not confirmed in time.</span>
                                            @elseif ($log->intake_status === 'Delayed')
                                                <span class="dose-status dose-status-delayed">Delayed</span>
                                                <span class="dose-note">This dose was marked late.</span>
                                            @else
                                                <span class="dose-status dose-status-neutral">{{ $log->intake_status }}</span>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="dose-empty">
                                This prescription does not have a generated dose schedule yet.
                            </div>
                        @endif
                    </div>
                </div>
            @empty
                <div style="padding: 1.5rem; border: 1px dashed #d1d5db; border-radius: 0.85rem; color: #6b7280; text-align: center;">
                    No prescriptions have been issued yet from your appointment sessions.
                </div>
            @endforelse
        </div>
    </div>
@endsection

@section('styles')
    <style>
        .metric-strip {
            display: grid;
            grid-template-columns: repeat(3, minmax(130px, 1fr));
            gap: 0.75rem;
            min-width: min(100%, 420px);
        }

        .metric-card,
        .detail-panel {
            padding: 0.95rem 1rem;
            border: 1px solid #e5e7eb;
            border-radius: 0.85rem;
            background: #f8fafc;
        }

        .metric-label,
        .detail-label {
            display: block;
            font-size: 0.78rem;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            margin-bottom: 0.35rem;
        }

        .metric-card strong,
        .detail-panel strong {
            color: #111827;
        }

        .prescription-list {
            display: grid;
            gap: 1rem;
        }

        .prescription-card {
            padding: 1.1rem;
            border: 1px solid #e5e7eb;
            border-radius: 0.95rem;
            background: #fff;
            box-shadow: 0 6px 18px rgba(15, 23, 42, 0.04);
        }

        .prescription-title {
            font-size: 1.05rem;
            font-weight: 700;
            color: #111827;
        }

        .issued-chip {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.35rem 0.75rem;
            border-radius: 999px;
            background: #dbeafe;
            color: #1d4ed8;
            font-size: 0.82rem;
            font-weight: 700;
        }

        .detail-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(140px, 1fr));
            gap: 0.85rem;
            margin-top: 1rem;
        }

        .detail-stack {
            display: grid;
            gap: 0.9rem;
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid #e5e7eb;
        }

        .adherence-panel {
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid #e5e7eb;
            display: grid;
            gap: 0.9rem;
        }

        .adherence-copy,
        .dose-copy {
            margin: 0.35rem 0 0 0;
            color: #6b7280;
            line-height: 1.5;
        }

        .adherence-summary-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(110px, 1fr));
            gap: 0.75rem;
        }

        .adherence-stat {
            padding: 0.85rem 0.95rem;
            border: 1px solid #e5e7eb;
            border-radius: 0.85rem;
            background: #f8fafc;
        }

        .dose-list {
            display: grid;
            gap: 0.75rem;
        }

        .dose-card {
            display: flex;
            justify-content: space-between;
            gap: 1rem;
            align-items: center;
            flex-wrap: wrap;
            padding: 0.95rem 1rem;
            border: 1px solid #e5e7eb;
            border-radius: 0.9rem;
            background: #f8fafc;
        }

        .dose-time {
            font-weight: 700;
            color: #111827;
        }

        .dose-action-group {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            flex-wrap: wrap;
            justify-content: flex-end;
        }

        .dose-status {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.35rem 0.7rem;
            border-radius: 999px;
            font-size: 0.8rem;
            font-weight: 700;
        }

        .dose-status-pending {
            background: #dbeafe;
            color: #1d4ed8;
        }

        .dose-status-upcoming {
            background: #e0f2fe;
            color: #0369a1;
        }

        .dose-status-confirmed {
            background: #dcfce7;
            color: #166534;
        }

        .dose-status-missed {
            background: #fee2e2;
            color: #991b1b;
        }

        .dose-status-delayed {
            background: #ffedd5;
            color: #9a3412;
        }

        .dose-status-neutral {
            background: #e5e7eb;
            color: #374151;
        }

        .dose-note {
            font-size: 0.85rem;
            color: #6b7280;
        }

        .dose-empty {
            padding: 0.95rem 1rem;
            border: 1px dashed #d1d5db;
            border-radius: 0.9rem;
            color: #6b7280;
            background: #f8fafc;
        }

        .history-link {
            color: #1d4ed8;
            font-weight: 700;
            text-decoration: none;
        }

        .history-link:hover {
            text-decoration: underline;
        }

        .detail-stack p {
            margin-top: 0.35rem;
            color: #374151;
            line-height: 1.55;
        }

        @media (max-width: 900px) {
            .metric-strip,
            .detail-grid,
            .adherence-summary-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endsection
