@extends('layouts.app')

@section('title', 'Medical Records')

@section('sidebar')
    <a href="{{ route('vet.dashboard') }}" class="sidebar-item">Dashboard</a>
    <a href="{{ route('vet.appointments') }}" class="sidebar-item">Appointments</a>

    <a href="{{ route('vet.medical-records') }}" class="sidebar-item active">Medical Records</a>
    <a href="{{ route('vet.prescriptions') }}" class="sidebar-item">E-Prescriptions</a>
    <a href="{{ route('vet.adherence-monitoring') }}" class="sidebar-item">Medication Adherence</a>
@endsection

@section('content')
    @php
        $patientCount = $patients->count();
        $totalPrescriptionCount = $medicalRecords->sum(fn ($record) => $record->prescriptions->count());
        $scheduledFollowUps = $medicalRecords->filter(fn ($record) => $record->follow_up_date && $record->follow_up_date->isFuture())->count();
        $selectedLatestRecord = $selectedPatientRecords->first();
    @endphp

    <section class="hero-card">
        <div class="hero-row">
            <div>
                <h1 class="hero-title">Medical Records</h1>
                <p class="hero-copy">
                    Review patient history, inspect treatment notes, and move directly into linked prescriptions from one clear workspace.
                </p>
            </div>

            <div class="metric-grid" style="min-width: min(100%, 460px);">
                <div class="metric-card">
                    <span class="metric-label">Patients</span>
                    <strong class="metric-value">{{ $patientCount }}</strong>
                </div>
                <div class="metric-card">
                    <span class="metric-label">Medical Records</span>
                    <strong class="metric-value">{{ $medicalRecords->count() }}</strong>
                </div>
                <div class="metric-card">
                    <span class="metric-label">Linked Prescriptions</span>
                    <strong class="metric-value">{{ $totalPrescriptionCount }}</strong>
                </div>
                <div class="metric-card">
                    <span class="metric-label">Follow-Ups</span>
                    <strong class="metric-value">{{ $scheduledFollowUps }}</strong>
                </div>
            </div>
        </div>
    </section>

    <div class="records-layout">
        <section class="card">
            <div class="surface-header">
                <div>
                    <h2>Patients</h2>
                    <p class="section-copy">Choose a patient to load the full medical record timeline.</p>
                </div>
            </div>

            <div class="stack">
                @forelse ($patients as $patient)
                    @php
                        $isSelected = optional($selectedPatient)->pet_id === $patient->pet_id;
                        $patientRecords = $medicalRecords->where('pet_id', $patient->pet_id);
                        $lastRecord = $patientRecords->first();
                    @endphp

                    <a href="{{ route('vet.medical-records', ['patient' => $patient->pet_id]) }}" class="patient-card{{ $isSelected ? ' patient-card-active' : '' }}">
                        <div class="action-row">
                            <div>
                                <div class="item-title">{{ $patient->name }}</div>
                                <p class="item-copy" style="margin-top: 0.3rem;">
                                    {{ $patient->species }}{{ $patient->breed ? ' - '.$patient->breed : '' }}
                                </p>
                            </div>
                            <span class="pill {{ $patientRecords->count() > 1 ? 'pill-neutral' : 'pill-success' }}">
                                {{ $patientRecords->count() }} record{{ $patientRecords->count() === 1 ? '' : 's' }}
                            </span>
                        </div>

                        <p class="item-copy" style="margin-top: 0.7rem;">Owner: {{ $patient->owner?->name ?? 'Unknown Owner' }}</p>
                        <p class="item-copy" style="margin-top: 0.35rem;">Last updated: {{ $lastRecord?->created_at?->format('M j, Y') ?? 'No records yet' }}</p>
                    </a>
                @empty
                    <div class="empty-state">
                        No patient medical records are available yet.
                    </div>
                @endforelse
            </div>
        </section>

        <section class="stack">
            @if ($selectedPatient)
                <div class="card">
                    <div class="surface-header">
                        <div>
                            <h2>{{ $selectedPatient->name }} Medical History</h2>
                            <p class="section-copy">
                                {{ $selectedPatient->species }}{{ $selectedPatient->breed ? ' - '.$selectedPatient->breed : '' }} / Owner: {{ $selectedPatient->owner?->name ?? 'Unknown Owner' }}
                            </p>
                        </div>

                        <a href="{{ route('vet.prescriptions', ['record' => $selectedLatestRecord?->record_id]) }}"
                            class="btn btn-secondary"
                            @if (!$selectedLatestRecord) style="pointer-events: none; opacity: 0.6;" @endif>
                            View Prescriptions
                        </a>
                    </div>

                    <div class="info-grid">
                        <div class="summary-card">
                            <span class="summary-label">Total Records</span>
                            <strong>{{ $selectedPatientRecords->count() }}</strong>
                            <span>{{ $selectedPatientPrescriptionCount }} linked prescription(s)</span>
                        </div>
                        <div class="summary-card">
                            <span class="summary-label">Latest Entry</span>
                            <strong>{{ $selectedLatestRecord?->created_at?->format('M j, Y') ?? 'No record yet' }}</strong>
                            <span>Medical record entry</span>
                        </div>
                        <div class="summary-card">
                            <span class="summary-label">Next Follow-Up</span>
                            <strong>{{ $selectedPatientRecords->first(fn ($record) => $record->follow_up_date && $record->follow_up_date->isFuture())?->follow_up_date?->format('M j, Y') ?? 'Not scheduled' }}</strong>
                            <span>Tracked from saved medical records</span>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="surface-header">
                        <div>
                            <h2>Record Timeline</h2>
                            <p class="section-copy">Each entry below is pulled from the medical records table and its linked appointment session data.</p>
                        </div>
                    </div>

                    <div class="list-grid">
                        @forelse ($selectedPatientRecords as $record)
                            @php
                                $recordSummary = $record->diagnosis ?: $record->treatment_plan ?: $record->vaccination_notes;
                            @endphp

                            <article class="record-card">
                                <div>
                                    <div class="item-meta" style="margin-top: 0;">
                                        <span class="pill {{ $record->prescriptions->count() > 0 ? 'pill-success' : 'pill-neutral' }}">
                                            {{ $record->prescriptions->count() }} prescription{{ $record->prescriptions->count() === 1 ? '' : 's' }}
                                        </span>
                                    </div>

                                    <div class="item-title" style="margin-top: 0.8rem;">
                                        {{ $record->diagnosis ?: 'Medical record '.$record->record_id }}
                                    </div>

                                    <p class="item-copy" style="margin-top: 0.35rem;">
                                        Created {{ $record->created_at?->format('F j, Y') ?? 'Unknown date' }}
                                    </p>

                                    <div class="info-grid" style="margin-top: 1rem;">
                                        <div class="detail-panel">
                                            <span class="detail-label">Diagnosis</span>
                                            <p>{{ $record->diagnosis ?: 'No diagnosis recorded.' }}</p>
                                        </div>
                                        <div class="detail-panel">
                                            <span class="detail-label">Follow-Up Date</span>
                                            <p>{{ $record->follow_up_date ? $record->follow_up_date->format('F j, Y') : 'No follow-up date scheduled.' }}</p>
                                        </div>
                                        <div class="detail-panel">
                                            <span class="detail-label">Treatment Plan</span>
                                            <p>{{ $record->treatment_plan ?: 'No treatment plan recorded.' }}</p>
                                        </div>
                                        <div class="detail-panel">
                                            <span class="detail-label">Vaccination Notes</span>
                                            <p>{{ $record->vaccination_notes ?: 'No vaccination notes recorded.' }}</p>
                                        </div>
                                    </div>

                                    @if ($recordSummary)
                                        <div class="detail-panel" style="margin-top: 1rem;">
                                            <span class="detail-label">Clinical Summary</span>
                                            <p>{{ $recordSummary }}</p>
                                        </div>
                                    @endif
                                </div>

                                <div class="stack" style="min-width: 210px;">
                                    <a href="{{ route('vet.prescriptions', ['record' => $record->record_id]) }}" class="btn btn-secondary">
                                        Open Prescriptions
                                    </a>
                                </div>
                            </article>
                        @empty
                            <div class="empty-state">
                                No medical records were found for {{ $selectedPatient->name }}.
                            </div>
                        @endforelse
                    </div>
                </div>
            @else
                <div class="card">
                    <div class="empty-state">
                        No medical records found for your appointment sessions yet.
                    </div>
                </div>
            @endif
        </section>
    </div>
@endsection

@section('styles')
    <style>
        .records-layout {
            display: grid;
            grid-template-columns: minmax(280px, 0.85fr) minmax(420px, 1.25fr);
            gap: 1.4rem;
            align-items: start;
        }

        .patient-card {
            display: block;
            padding: 1.1rem;
            border: 1px solid var(--shell-line);
            border-radius: var(--shell-radius-lg);
            background: rgba(255, 255, 255, 0.86);
            transition: transform 0.18s ease, border-color 0.18s ease, box-shadow 0.18s ease;
        }

        .patient-card:hover {
            transform: translateY(-1px);
            border-color: rgba(15, 139, 141, 0.26);
            box-shadow: 0 14px 30px rgba(23, 49, 61, 0.08);
        }

        .patient-card-active {
            border-color: rgba(15, 139, 141, 0.4);
            background: linear-gradient(180deg, rgba(15, 139, 141, 0.08), rgba(255, 255, 255, 0.92));
        }

        @media (max-width: 1100px) {
            .records-layout {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endsection
