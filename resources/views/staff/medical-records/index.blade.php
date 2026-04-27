@extends('layouts.app')

@section('title', 'Staff Medical Records')

@section('sidebar')
    @include('staff.sidebar')
@endsection

@section('content')
    @php
        $pageRecords = $medicalRecords->getCollection();
        $patientCount = $pageRecords->pluck('pet.pet_id')->filter()->unique()->count();
        $linkedPrescriptionCount = $pageRecords->sum(fn ($record) => $record->prescriptions->count());
        $scheduledFollowUps = $pageRecords->filter(fn ($record) => $record->follow_up_date && $record->follow_up_date->isFuture())->count();
    @endphp

    <section class="hero-card">
        <div class="hero-row">
            <div>
                <h1 class="hero-title">Medical Records</h1>
                <p class="hero-copy">
                    Review patient histories, check the assigned veterinarian, and open linked prescriptions directly from the staff workspace.
                </p>
            </div>

            <div class="metric-grid" style="min-width: min(100%, 460px);">
                <div class="metric-card">
                    <span class="metric-label">Total Results</span>
                    <strong class="metric-value">{{ $medicalRecords->total() }}</strong>
                </div>
                <div class="metric-card">
                    <span class="metric-label">Patients On Page</span>
                    <strong class="metric-value">{{ $patientCount }}</strong>
                </div>
                <div class="metric-card">
                    <span class="metric-label">Linked Prescriptions</span>
                    <strong class="metric-value">{{ $linkedPrescriptionCount }}</strong>
                </div>
                <div class="metric-card">
                    <span class="metric-label">Follow-Ups</span>
                    <strong class="metric-value">{{ $scheduledFollowUps }}</strong>
                </div>
            </div>
        </div>
    </section>

    <section class="card">
        <div class="surface-header">
            <div>
                <h2>Filter Medical Records</h2>
                <p class="section-copy">Search by patient or species and narrow the record timeline when needed.</p>
            </div>
        </div>

        <form method="GET" action="{{ route('staff.medical-records') }}" class="form-grid">
            <div class="field">
                <label class="field-label" for="search">Patient Or Species</label>
                <input id="search" name="search" type="text" class="field-control" value="{{ request('search') }}" placeholder="Peanut or Dog">
            </div>

            <div class="field">
                <label class="field-label" for="from_date">Created From</label>
                <input id="from_date" name="from_date" type="date" class="field-control" value="{{ request('from_date') }}">
            </div>

            <div class="field">
                <label class="field-label" for="to_date">Created To</label>
                <input id="to_date" name="to_date" type="date" class="field-control" value="{{ request('to_date') }}">
            </div>

            <div class="action-row" style="align-items: end;">
                <button type="submit" class="btn btn-primary">Apply Filters</button>
                <a href="{{ route('staff.medical-records') }}" class="btn btn-secondary">Reset</a>
            </div>
        </form>
    </section>

    <section class="card">
        <div class="surface-header">
            <div>
                <h2>Record List</h2>
                <p class="section-copy">Open a record to view consultation notes, follow-up dates, and linked prescriptions.</p>
            </div>
        </div>

        <div class="list-grid">
            @forelse ($medicalRecords as $medicalRecord)
                @php
                    $pet = $medicalRecord->pet;
                    $owner = $pet?->owner;
                    $veterinarian = $medicalRecord->consultation?->veterinarian;
                    $latestPrescription = $medicalRecord->prescriptions->sortByDesc('issued_at')->first();
                @endphp

                <article class="record-card">
                    <div>
                        <div class="action-row">
                            <div>
                                <div class="item-title">{{ $pet?->name ?? 'Unknown Pet' }}</div>
                                <p class="item-copy" style="margin-top: 0.3rem;">
                                    {{ $pet?->species ?? 'Unknown species' }}{{ $pet?->breed ? ' - '.$pet->breed : '' }}
                                </p>
                            </div>

                            <span class="pill {{ $medicalRecord->prescriptions->count() > 0 ? 'pill-success' : 'pill-neutral' }}">
                                {{ $medicalRecord->prescriptions->count() }} Rx
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
                                <span class="detail-label">Created</span>
                                <p>{{ $medicalRecord->created_at?->format('M j, Y') ?? 'Unknown date' }}</p>
                            </div>
                            <div class="detail-panel">
                                <span class="detail-label">Follow-Up</span>
                                <p>{{ $medicalRecord->follow_up_date?->format('M j, Y') ?? 'Not scheduled' }}</p>
                            </div>
                        </div>

                        <div class="detail-panel" style="margin-top: 1rem;">
                            <span class="detail-label">Diagnosis</span>
                            <p>{{ $medicalRecord->diagnosis ?: 'No diagnosis recorded.' }}</p>
                        </div>
                    </div>

                    <div class="stack record-actions">
                        <a href="{{ route('staff.medical-records.details', $medicalRecord->record_id) }}" class="btn btn-primary">Open Record</a>
                        @if ($latestPrescription)
                            <a href="{{ route('staff.prescriptions.details', $latestPrescription->prescription_id) }}" class="btn btn-secondary">Latest Prescription</a>
                        @endif
                    </div>
                </article>
            @empty
                <div class="empty-state">
                    No medical records matched the current filters.
                </div>
            @endforelse
        </div>
    </section>

    @if ($medicalRecords->hasPages())
        <section class="card">
            {{ $medicalRecords->withQueryString()->links() }}
        </section>
    @endif
@endsection

@section('styles')
    <style>
        .record-card {
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(220px, 250px);
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
