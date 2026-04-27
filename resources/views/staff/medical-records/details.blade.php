@extends('layouts.app')

@section('title', 'Medical Record Details')

@section('sidebar')
    @include('staff.sidebar')
@endsection

@section('content')
    @php
        $pet = $medicalRecord->pet;
        $owner = $pet?->owner;
        $consultation = $medicalRecord->consultation;
        $veterinarian = $consultation?->veterinarian;
    @endphp

    <section class="hero-card">
        <div class="hero-row">
            <div>
                <h1 class="hero-title">{{ $pet?->name ?? 'Medical Record' }}</h1>
                <p class="hero-copy">
                    Review the consultation summary, treatment plan, vaccination notes, and every prescription linked to this record.
                </p>
            </div>

            <div class="action-row">
                <a href="{{ route('staff.medical-records') }}" class="btn btn-secondary">Back To Medical Records</a>
            </div>
        </div>
    </section>

    <div class="info-grid">
        <div class="summary-card">
            <span class="summary-label">Patient</span>
            <strong>{{ $pet?->name ?? 'Unknown Pet' }}</strong>
            <span>{{ $pet?->species ?? 'Unknown species' }}{{ $pet?->breed ? ' - '.$pet->breed : '' }}</span>
        </div>
        <div class="summary-card">
            <span class="summary-label">Owner</span>
            <strong>{{ $owner?->name ?? 'Unknown Owner' }}</strong>
            <span>{{ $owner?->email ?? 'No email on file' }}</span>
        </div>
        <div class="summary-card">
            <span class="summary-label">Veterinarian</span>
            <strong>{{ $veterinarian?->name ? 'Dr. '.$veterinarian->name : 'Not assigned' }}</strong>
            <span>{{ $consultation?->consultation_date?->format('M j, Y g:i A') ?? 'No consultation date recorded' }}</span>
        </div>
        <div class="summary-card">
            <span class="summary-label">Record</span>
            <strong>Record {{ $medicalRecord->record_id }}</strong>
            <span>{{ $medicalRecord->prescriptions->count() }} linked prescription{{ $medicalRecord->prescriptions->count() === 1 ? '' : 's' }}</span>
        </div>
    </div>

    <section class="card" style="margin-top: 1.2rem;">
        <div class="surface-header">
            <div>
                <h2>Clinical Details</h2>
                <p class="section-copy">Core medical information saved during or after the consultation.</p>
            </div>
        </div>

        <div class="info-grid">
            <div class="detail-panel">
                <span class="detail-label">Diagnosis</span>
                <p>{{ $medicalRecord->diagnosis ?: 'No diagnosis recorded.' }}</p>
            </div>
            <div class="detail-panel">
                <span class="detail-label">Treatment Plan</span>
                <p>{{ $medicalRecord->treatment_plan ?: 'No treatment plan recorded.' }}</p>
            </div>
            <div class="detail-panel">
                <span class="detail-label">Vaccination Notes</span>
                <p>{{ $medicalRecord->vaccination_notes ?: 'No vaccination notes recorded.' }}</p>
            </div>
            <div class="detail-panel">
                <span class="detail-label">Follow-Up Date</span>
                <p>{{ $medicalRecord->follow_up_date?->format('F j, Y') ?? 'Not scheduled' }}</p>
            </div>
        </div>

        <div class="info-grid" style="margin-top: 1rem;">
            <div class="detail-panel">
                <span class="detail-label">Chief Complaint</span>
                <p>{{ $consultation?->chief_complaint ?: 'No chief complaint recorded.' }}</p>
            </div>
            <div class="detail-panel">
                <span class="detail-label">Consultation Notes</span>
                <p>{{ $consultation?->consultation_notes ?: 'No consultation notes recorded.' }}</p>
            </div>
        </div>
    </section>

    <section class="card" style="margin-top: 1.2rem;">
        <div class="surface-header">
            <div>
                <h2>Linked Prescriptions</h2>
                <p class="section-copy">Medication created from this medical record appears below.</p>
            </div>
        </div>

        <div class="list-grid">
            @forelse ($medicalRecord->prescriptions as $prescription)
                @php
                    $logCount = $prescription->adherenceLogs->count();
                    $takenRate = $logCount > 0
                        ? round(($prescription->adherenceLogs->where('intake_status', 'Taken')->count() / $logCount) * 100)
                        : null;
                @endphp

                <article class="list-card">
                    <div>
                        <div class="item-title">{{ $prescription->medication_name }}</div>
                        <p class="item-copy" style="margin-top: 0.35rem;">
                            {{ $prescription->dosage }} / {{ $prescription->frequency }} / {{ $prescription->duration }}
                        </p>
                        <p class="item-copy" style="margin-top: 0.35rem;">
                            Issued {{ $prescription->issued_at?->format('M j, Y g:i A') ?? 'Unknown date' }}
                        </p>
                    </div>

                    <div class="stack" style="min-width: 220px;">
                        <span class="pill {{ $takenRate !== null && $takenRate >= 80 ? 'pill-success' : 'pill-neutral' }}">
                            {{ $takenRate !== null ? $takenRate.'% adherence' : 'No logs yet' }}
                        </span>
                        <a href="{{ route('staff.prescriptions.details', $prescription->prescription_id) }}" class="btn btn-primary">Open Prescription</a>
                    </div>
                </article>
            @empty
                <div class="empty-state">
                    No prescriptions are linked to this medical record yet.
                </div>
            @endforelse
        </div>
    </section>
@endsection
