@extends('layouts.app')

@section('title', 'Staff Prescriptions')

@section('sidebar')
    @include('staff.sidebar')
@endsection

@section('content')
    @php
        $pagePrescriptions = $prescriptions->getCollection();
        $patientCount = $pagePrescriptions->pluck('medicalRecord.pet.pet_id')->filter()->unique()->count();
        $withLogsCount = $pagePrescriptions->filter(fn ($prescription) => $prescription->adherenceLogs->isNotEmpty())->count();
        $latestIssuedAt = $pagePrescriptions->first()?->issued_at;
    @endphp

    <section class="hero-card">
        <div class="hero-row">
            <div>
                <h1 class="hero-title">Prescriptions</h1>
                <p class="hero-copy">
                    Review every medication issued through clinic medical records, inspect the linked patient data, and open adherence history when needed.
                </p>
            </div>

            <div class="metric-grid" style="min-width: min(100%, 460px);">
                <div class="metric-card">
                    <span class="metric-label">Total Results</span>
                    <strong class="metric-value">{{ $prescriptions->total() }}</strong>
                </div>
                <div class="metric-card">
                    <span class="metric-label">Patients On Page</span>
                    <strong class="metric-value">{{ $patientCount }}</strong>
                </div>
                <div class="metric-card">
                    <span class="metric-label">With Logs</span>
                    <strong class="metric-value">{{ $withLogsCount }}</strong>
                </div>
                <div class="metric-card">
                    <span class="metric-label">Latest Issued</span>
                    <strong class="metric-value">{{ $latestIssuedAt?->format('M j, Y') ?? 'No data' }}</strong>
                </div>
            </div>
        </div>
    </section>

    <section class="card">
        <div class="surface-header">
            <div>
                <h2>Filter Prescriptions</h2>
                <p class="section-copy">Search by medication or patient, then narrow the timeline if you need a smaller list.</p>
            </div>
        </div>

        <form method="GET" action="{{ route('staff.prescriptions') }}" class="form-grid">
            <div class="field">
                <label class="field-label" for="search">Medication Or Patient</label>
                <input id="search" name="search" type="text" class="field-control" value="{{ request('search') }}" placeholder="Cetirizine or Peanut">
            </div>

            <div class="field">
                <label class="field-label" for="from_date">Issued From</label>
                <input id="from_date" name="from_date" type="date" class="field-control" value="{{ request('from_date') }}">
            </div>

            <div class="field">
                <label class="field-label" for="to_date">Issued To</label>
                <input id="to_date" name="to_date" type="date" class="field-control" value="{{ request('to_date') }}">
            </div>

            <div class="action-row" style="align-items: end;">
                <button type="submit" class="btn btn-primary">Apply Filters</button>
                <a href="{{ route('staff.prescriptions') }}" class="btn btn-secondary">Reset</a>
            </div>
        </form>
    </section>

    <section class="card">
        <div class="surface-header">
            <div>
                <h2>Prescription List</h2>
                <p class="section-copy">Open any prescription to inspect the linked medical record and adherence activity.</p>
            </div>
        </div>

        <div class="list-grid">
            @forelse ($prescriptions as $prescription)
                @php
                    $record = $prescription->medicalRecord;
                    $pet = $record?->pet;
                    $owner = $pet?->owner;
                    $veterinarian = $record?->consultation?->veterinarian;
                    $adherenceLogs = $prescription->adherenceLogs;
                    $adherenceRate = $adherenceLogs->count() > 0
                        ? round(($adherenceLogs->where('intake_status', 'Taken')->count() / $adherenceLogs->count()) * 100)
                        : null;
                @endphp

                <article class="record-card">
                    <div>
                        <div class="action-row">
                            <div>
                                <div class="item-title">{{ $prescription->medication_name }}</div>
                                <p class="item-copy" style="margin-top: 0.3rem;">
                                    {{ $pet?->name ?? 'Unknown Pet' }}{{ $pet?->species ? ' / '.$pet->species : '' }}{{ $pet?->breed ? ' - '.$pet->breed : '' }}
                                </p>
                            </div>

                            <span class="pill {{ $adherenceRate !== null && $adherenceRate >= 80 ? 'pill-success' : 'pill-neutral' }}">
                                {{ $adherenceRate !== null ? $adherenceRate.'% adherence' : 'No logs yet' }}
                            </span>
                        </div>

                        <div class="info-grid" style="margin-top: 1rem;">
                            <div class="detail-panel">
                                <span class="detail-label">Owner</span>
                                <p>{{ $owner?->name ?? 'Unknown Owner' }}</p>
                            </div>
                            <div class="detail-panel">
                                <span class="detail-label">Veterinarian</span>
                                <p>{{ $veterinarian?->name ? 'Dr. '.$veterinarian->name : 'Not assigned' }}</p>
                            </div>
                            <div class="detail-panel">
                                <span class="detail-label">Medical Record</span>
                                <p>{{ $record?->record_id ? 'Record '.$record->record_id : 'No record linked' }}</p>
                            </div>
                            <div class="detail-panel">
                                <span class="detail-label">Issued</span>
                                <p>{{ $prescription->issued_at?->format('M j, Y g:i A') ?? 'Unknown date' }}</p>
                            </div>
                        </div>

                        <p class="item-copy" style="margin-top: 1rem;">
                            {{ $prescription->dosage }} / {{ $prescription->frequency }} / {{ $prescription->duration }}
                        </p>

                        <p class="item-copy" style="margin-top: 0.35rem;">
                            Diagnosis: {{ $record?->diagnosis ?: 'No diagnosis recorded.' }}
                        </p>
                    </div>

                    <div class="stack record-actions">
                        <span class="pill pill-neutral">{{ $adherenceLogs->count() }} adherence log{{ $adherenceLogs->count() === 1 ? '' : 's' }}</span>
                        <a href="{{ route('staff.prescriptions.details', $prescription->prescription_id) }}" class="btn btn-primary">Open Prescription</a>
                        @if ($record)
                            <a href="{{ route('staff.medical-records.details', $record->record_id) }}" class="btn btn-secondary">View Medical Record</a>
                        @endif
                    </div>
                </article>
            @empty
                <div class="empty-state">
                    No prescriptions matched the current filters.
                </div>
            @endforelse
        </div>
    </section>

    @if ($prescriptions->hasPages())
        <section class="card">
            {{ $prescriptions->withQueryString()->links() }}
        </section>
    @endif
@endsection

@section('styles')
    <style>
        .record-card {
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(220px, 260px);
            gap: 1.25rem;
            padding: 1.25rem;
            border: 1px solid var(--shell-line);
            border-radius: var(--shell-radius-lg);
            background: rgba(255, 255, 255, 0.88);
        }

        .record-actions {
            min-width: 0;
            justify-content: center;
        }

        @media (max-width: 960px) {
            .record-card {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endsection
