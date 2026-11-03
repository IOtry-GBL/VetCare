@extends('layouts.app')

@section('title', 'Prescription Details')

@section('sidebar')
    @include('staff.sidebar')
@endsection

@section('content')
    @php
        $record = $prescription->medicalRecord;
        $pet = $record?->pet;
        $owner = $pet?->owner;
        $veterinarian = $record?->consultation?->veterinarian;
        $adherenceLogs = $prescription->adherenceLogs;
    @endphp

    <section class="print-sheet">
        @include('components.print-clinic-header')
        <div class="print-document-heading">
            <h2>PRESCRIPTION</h2>
            <span>Issued {{ $prescription->issued_at?->format('M d, Y') ?? 'Date not recorded' }}</span>
        </div>

        <div class="print-patient-grid">
            <div class="print-field"><span>Owner:</span><strong>{{ $owner?->name ?? 'Unknown Owner' }}</strong></div>
            <div class="print-field"><span>Pet:</span><strong>{{ $pet?->name ?? 'Unknown Pet' }}</strong></div>
            <div class="print-field"><span>Species:</span><strong>{{ $pet?->species ?? 'N/A' }}</strong></div>
            <div class="print-field"><span>Breed:</span><strong>{{ $pet?->breed ?? 'N/A' }}</strong></div>
            <div class="print-field"><span>Record No.:</span><strong>{{ $record?->record_id ?? 'N/A' }}</strong></div>
            <div class="print-field"><span>Veterinarian:</span><strong>{{ $veterinarian?->name ? 'Dr. '.$veterinarian->name : 'Not assigned' }}</strong></div>
        </div>

        <div class="print-prescription-section">
            <h3>Medication Instructions</h3>
            <table>
                <thead>
                    <tr><th>Medication</th><th>Dosage</th><th>Frequency</th><th>Duration</th></tr>
                </thead>
                <tbody>
                    <tr>
                        <td>{{ $prescription->medication_name }}</td>
                        <td>{{ $prescription->dosage }}</td>
                        <td>{{ $prescription->frequency }}</td>
                        <td>{{ $prescription->duration }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="print-narrative">
            <div>
                <h3>Diagnosis</h3>
                <p>{{ $record?->diagnosis ?: 'Not recorded' }}</p>
            </div>
            <div>
                <h3>Treatment Plan</h3>
                <p>{{ $record?->treatment_plan ?: 'Not recorded' }}</p>
            </div>
            <div>
                <h3>Follow-up</h3>
                <p>{{ $record?->follow_up_date?->format('M d, Y') ?? 'Not scheduled' }}</p>
            </div>
        </div>

        <div class="print-signature-row">
            <span>Veterinarian signature: ___________________________________</span>
            <span>Date: __________________</span>
        </div>
    </section>

    <section class="hero-card">
        <div class="hero-row">
            <div>
                <h1 class="hero-title">{{ $prescription->medication_name }}</h1>
                <p class="hero-copy">
                    Review the medication instructions, linked patient record, and adherence history from the staff workspace.
                </p>
            </div>

            <div class="action-row">
                <button type="button" class="btn btn-primary screen-only" onclick="window.print()">Print Prescription</button>
                <a href="{{ route('staff.prescriptions') }}" class="btn btn-secondary screen-only">Back To Prescriptions</a>
                @if ($record)
                    <a href="{{ route('staff.medical-records.details', $record->record_id) }}" class="btn btn-primary screen-only">Open Medical Record</a>
                @endif
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
            <span>{{ $record?->record_id ? 'Record '.$record->record_id : 'No medical record linked' }}</span>
        </div>
        <div class="summary-card">
            <span class="summary-label">Adherence</span>
            <strong>{{ $adherencePercentage }}%</strong>
            <span>{{ $adherenceLogs->count() }} logged dose{{ $adherenceLogs->count() === 1 ? '' : 's' }}</span>
        </div>
    </div>

    <div class="details-grid">
        <section class="card">
            <div class="surface-header">
                <div>
                    <h2>Medication Details</h2>
                    <p class="section-copy">These instructions are stored directly on the e-prescription record.</p>
                </div>
            </div>

            <div class="info-grid">
                <div class="detail-panel">
                    <span class="detail-label">Dosage</span>
                    <p>{{ $prescription->dosage }}</p>
                </div>
                <div class="detail-panel">
                    <span class="detail-label">Frequency</span>
                    <p>{{ $prescription->frequency }}</p>
                </div>
                <div class="detail-panel">
                    <span class="detail-label">Duration</span>
                    <p>{{ $prescription->duration }}</p>
                </div>
                <div class="detail-panel">
                    <span class="detail-label">Issued At</span>
                    <p>{{ $prescription->issued_at?->format('F j, Y g:i A') ?? 'Unknown date' }}</p>
                </div>
            </div>
        </section>

        <section class="card">
            <div class="surface-header">
                <div>
                    <h2>Linked Medical Record</h2>
                    <p class="section-copy">The prescription below is tied to this diagnosis and treatment plan.</p>
                </div>
            </div>

            <div class="info-grid">
                <div class="detail-panel">
                    <span class="detail-label">Diagnosis</span>
                    <p>{{ $record?->diagnosis ?: 'No diagnosis recorded.' }}</p>
                </div>
                <div class="detail-panel">
                    <span class="detail-label">Treatment Plan</span>
                    <p>{{ $record?->treatment_plan ?: 'No treatment plan recorded.' }}</p>
                </div>
                <div class="detail-panel">
                    <span class="detail-label">Vaccination Notes</span>
                    <p>{{ $record?->vaccination_notes ?: 'No vaccination notes recorded.' }}</p>
                </div>
                <div class="detail-panel">
                    <span class="detail-label">Follow-Up</span>
                    <p>{{ $record?->follow_up_date?->format('F j, Y') ?? 'Not scheduled' }}</p>
                </div>
            </div>
        </section>
    </div>

    <section class="card" style="margin-top: 1.2rem;">
        <div class="surface-header">
            <div>
                <h2>Adherence History</h2>
                <p class="section-copy">Track whether doses were taken, missed, delayed, or still pending.</p>
            </div>
        </div>

        <div class="list-grid">
            @forelse ($adherenceLogs as $log)
                <article class="list-card">
                    <div>
                        <div class="item-title">{{ $log->scheduled_datetime?->format('M j, Y g:i A') ?? 'Scheduled dose' }}</div>
                        <p class="item-copy" style="margin-top: 0.35rem;">
                            Confirmation: {{ $log->confirmation_time?->format('M j, Y g:i A') ?? 'Not yet confirmed' }}
                        </p>
                        @if ($log->remarks)
                            <p class="item-copy" style="margin-top: 0.35rem;">Remarks: {{ $log->remarks }}</p>
                        @endif
                    </div>

                    <div class="action-row" style="justify-content: flex-end;">
                        <span class="pill {{ $log->intake_status === 'Taken' ? 'pill-success' : 'pill-neutral' }}">
                            {{ $log->intake_status }}
                        </span>
                    </div>
                </article>
            @empty
                <div class="empty-state">
                    No adherence logs have been recorded for this prescription yet.
                </div>
            @endforelse
        </div>
    </section>
@endsection

@section('styles')
    @include('components.print-sheet-styles')
    <style>
        .details-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
            margin-top: 1.2rem;
        }

        @media (max-width: 1024px) {
            .details-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endsection

@if (request()->boolean('print'))
    <script>
        window.addEventListener('load', () => window.print());
    </script>
@endif
