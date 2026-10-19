@extends('layouts.app')

@section('title', 'Medical Record Report')

@section('sidebar')
    <a href="{{ route('vet.dashboard') }}" class="sidebar-item">Dashboard</a>
    <a href="{{ route('vet.appointments') }}" class="sidebar-item">Appointments</a>
    <a href="{{ route('vet.medical-records') }}" class="sidebar-item active">Medical Records</a>
    <a href="{{ route('vet.prescriptions') }}" class="sidebar-item">E-Prescriptions</a>
    <a href="{{ route('vet.adherence-monitoring') }}" class="sidebar-item">Medication Adherence</a>
@endsection

@section('content')
    @php
        $pet = $record->pet;
        $owner = $pet?->owner;
        $veterinarian = $record->consultation?->veterinarian;
        $reasonForVisit = $record->consultation?->appointment?->reason_for_visit;
        $recordAdherenceLogs = $record->prescriptions->flatMap->adherenceLogs;
        $takenDoses = $recordAdherenceLogs->where('intake_status', 'Taken')->count();
        $totalDoses = $recordAdherenceLogs->count();
        $adherenceRate = $totalDoses > 0 ? round(($takenDoses / $totalDoses) * 100) : null;
        $visitType = $record->consultation?->appointment?->consultation_mode ?? 'In-clinic';
        $consultationDate = $record->consultation?->consultation_date ?? $record->consultation?->appointment?->appointment_date;
        $familyName = $owner?->name ?? 'Not on record';
        $address = $owner?->address ?? 'Not on record';
        $cellNumber = $owner?->phone ?? $owner?->contact_no ?? 'N/A';
        $weight = $record->weight !== null ? $record->weight.' kg' : 'Not recorded';
        $temp = $record->temp !== null ? $record->temp.'°C' : 'Not recorded';
        $fee = $record->fee !== null ? '₱'.number_format($record->fee, 2) : 'Not recorded';
    @endphp

    <section class="hero-card">
        <div class="hero-row">
            <div>
                <h1 class="hero-title">Record No. {{ $record->record_id }}</h1>
                <p class="hero-copy">
                    Medical report for {{ $pet?->name ?? 'Unknown Pet' }}.
                </p>
            </div>

            <div class="action-row">
                <button type="button" class="btn btn-primary screen-only" onclick="window.print()">Print Record</button>
                <a href="{{ route('vet.medical-records') }}" class="btn btn-secondary">Back To Record List</a>
            </div>
        </div>
    </section>

    <article class="card medical-report">
        <div class="report-section-title">
            <div>
                <span class="eyebrow">Clinical Report</span>
                <h3>{{ $reasonForVisit ?: 'Medical record details' }}</h3>
            </div>
            <span class="report-stamp">Record #{{ $record->record_id }}</span>
        </div>

        <div class="clinical-layout">
            <div class="clinical-panel">
                <div class="label-row">
                    <span>Family Name:</span>
                    <strong>{{ $familyName }}</strong>
                </div>
                <div class="label-row">
                    <span>Address:</span>
                    <strong>{{ $address }}</strong>
                </div>
                <div class="label-row">
                    <span>Species:</span>
                    <strong>{{ $pet?->species ?? 'N/A' }}</strong>
                </div>
                <div class="label-row">
                    <span>Breed:</span>
                    <strong>{{ $pet?->breed ?? 'N/A' }}</strong>
                </div>
                <div class="label-row">
                    <span>Birthday:</span>
                    <strong>{{ $pet?->date_of_birth?->format('M d, Y') ?? 'N/A' }}</strong>
                </div>
                <div class="label-row">
                    <span>Age:</span>
                    <strong>{{ $pet?->age_display ?? ($pet?->age !== null ? $pet->age.' years' : 'N/A') }}</strong>
                </div>
                <div class="label-row">
                    <span>Sex:</span>
                    <strong>{{ $pet?->sex ?? 'N/A' }}</strong>
                </div>
                <div class="label-row">
                    <span>Color:</span>
                    <strong>{{ $pet?->color ?? 'Not on record' }}</strong>
                </div>
                <div class="label-row">
                    <span>Food:</span>
                    <strong>{{ $record->food ?: 'Not recorded' }}</strong>
                </div>
                <div class="label-row">
                    <span>Environment:</span>
                    <strong>{{ $record->environment ?: 'Not recorded' }}</strong>
                </div>
                <div class="label-row label-row--multi">
                    <span>Case/History:</span>
                    <strong>{{ $record->diagnosis ?: 'No case history recorded.' }}</strong>
                </div>
                <div class="label-row label-row--multi">
                    <span>Line of Treatment:</span>
                    <strong>{{ $record->treatment_plan ?: 'No treatment plan recorded.' }}</strong>
                </div>
                <div class="label-row">
                    <span>Attending Vet:</span>
                    <strong>{{ $veterinarian?->name ? 'Dr. '.$veterinarian->name : 'Not assigned' }}</strong>
                </div>
                <div class="label-row">
                    <span>Fee:</span>
                    <strong>{{ $fee }}</strong>
                </div>
                <div class="label-row">
                    <span>Follow update:</span>
                    <strong>{{ $record->follow_up_date?->format('M d, Y') ?? 'Not scheduled' }}</strong>
                </div>
            </div>

            <div class="clinical-panel clinical-panel--secondary">
                <div class="label-row">
                    <span>Visit type:</span>
                    <strong>{{ $visitType }}</strong>
                </div>
                <div class="label-row">
                    <span>Date:</span>
                    <strong>{{ $consultationDate?->format('M d, Y') ?? 'N/A' }}</strong>
                </div>
                <div class="label-row">
                    <span>Cell#:</span>
                    <strong>{{ $cellNumber }}</strong>
                </div>
                <div class="label-row">
                    <span>Weight:</span>
                    <strong>{{ $weight }}</strong>
                </div>
                <div class="label-row">
                    <span>Temp:</span>
                    <strong>{{ $temp }}</strong>
                </div>
                <div class="label-row label-row--ghost"></div>
                <div class="label-row">
                    <span>Vaccination Notes:</span>
                    <strong>{{ $record->vaccination_notes ?: 'Not recorded' }}</strong>
                </div>
                <div class="label-row label-row--ghost"></div>
                <div class="label-row label-row--ghost"></div>
                <div class="label-row label-row--ghost"></div>
            </div>
        </div>

        <div class="report-subsection">
            <div class="report-section-head compact">
                <div>
                    <span class="detail-label">Prescription Records And Adherence Percentage</span>
                    <p class="section-copy">Prescription records linked to this medical record with adherence percentage per prescription.</p>
                </div>
                <span class="pill {{ $record->prescriptions->count() > 0 ? 'pill-success' : 'pill-neutral' }}">
                    {{ $record->prescriptions->count() }} item{{ $record->prescriptions->count() === 1 ? '' : 's' }}
                </span>
            </div>

            @if ($record->prescriptions->isNotEmpty())
                <div class="mini-table-wrap">
                    <table class="mini-table">
                        <thead>
                            <tr>
                                <th>Medication</th>
                                <th>Dosage</th>
                                <th>Schedule</th>
                                <th>Issued</th>
                                <th>Adherence</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($record->prescriptions as $prescription)
                                @php
                                    $prescriptionDoseTotal = $prescription->adherenceLogs->count();
                                    $prescriptionDoseTaken = $prescription->adherenceLogs->where('intake_status', 'Taken')->count();
                                    $prescriptionAdherence = $prescriptionDoseTotal > 0
                                        ? round(($prescriptionDoseTaken / $prescriptionDoseTotal) * 100)
                                        : null;
                                @endphp
                                <tr>
                                    <td>{{ $prescription->medication_name }}</td>
                                    <td>{{ $prescription->dosage }}</td>
                                    <td>{{ $prescription->frequency }} / {{ $prescription->duration }}</td>
                                    <td>{{ $prescription->issued_at?->format('M j, Y') ?? 'Not recorded' }}</td>
                                    <td>{{ $prescriptionAdherence !== null ? $prescriptionAdherence.'%' : 'No data' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="empty-state">No prescriptions are linked to this medical record.</div>
            @endif
        </div>

        <div class="report-subsection">
            <div class="report-section-head compact">
                <div>
                    <span class="detail-label">Medical Adherence Record</span>
                    <p class="section-copy">Overall dose adherence for prescriptions linked to this medical record.</p>
                </div>
                <span class="pill {{ $adherenceRate !== null && $adherenceRate >= 80 ? 'pill-success' : 'pill-warning' }}">
                    {{ $adherenceRate !== null ? $adherenceRate.'%' : 'No data' }}
                </span>
            </div>

            <div class="adherence-report-grid">
                <div class="report-field">
                    <span class="summary-label">Taken</span>
                    <strong>{{ $takenDoses }}</strong>
                </div>
                <div class="report-field">
                    <span class="summary-label">Total Amount To Be Taken</span>
                    <strong>{{ $totalDoses }}</strong>
                </div>
                <div class="report-field">
                    <span class="summary-label">Percentage</span>
                    <strong>{{ $adherenceRate !== null ? $adherenceRate.'%' : 'No data' }}</strong>
                </div>
            </div>
        </div>
    </article>
    @if (request()->boolean('print'))
        <script>
            window.addEventListener('load', () => window.print());
        </script>
    @endif
@endsection

@section('styles')
    <style>
        @media print {
            body * {
                visibility: hidden !important;
            }

            .medical-report,
            .medical-report * {
                visibility: visible !important;
            }

            .medical-report {
                position: absolute;
                inset: 0 auto auto 0;
                width: 100%;
                margin: 0 !important;
                box-shadow: none !important;
            }

            .mini-table-wrap {
                overflow: visible;
            }

            .mini-table {
                min-width: 0;
            }
        }

        .medical-report {
            background: rgba(255, 255, 255, 0.94);
        }

        .report-section-title {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            padding-bottom: 1rem;
            margin-bottom: 1rem;
            border-bottom: 1px solid var(--shell-line);
        }

        .report-section-title h3 {
            margin: 0.15rem 0 0;
            font-size: 1.45rem;
            color: var(--shell-ink);
        }

        .report-stamp {
            display: inline-flex;
            align-items: center;
            min-height: 34px;
            padding: 0.35rem 0.8rem;
            border-radius: 999px;
            background: rgba(15, 139, 141, 0.12);
            color: var(--shell-accent-deep);
            font-size: 0.8rem;
            font-weight: 700;
        }

        .clinical-layout {
            display: grid;
            grid-template-columns: minmax(0, 1.15fr) minmax(240px, 0.85fr);
            gap: 1.25rem;
            margin-top: 0.75rem;
        }

        .clinical-panel {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            padding: 1rem 1.1rem;
            border: 1px solid var(--shell-line);
            border-radius: var(--shell-radius-lg);
            background: rgba(248, 250, 252, 0.8);
        }

        .clinical-panel--secondary {
            background: linear-gradient(180deg, rgba(248, 250, 252, 0.9), rgba(255, 255, 255, 0.8));
        }

        .label-row {
            display: grid;
            grid-template-columns: minmax(120px, 150px) minmax(0, 1fr);
            gap: 0.85rem;
            align-items: flex-start;
            padding: 0.55rem 0;
            border-bottom: 1px solid rgba(15, 23, 42, 0.06);
        }

        .label-row:last-child {
            border-bottom: none;
        }

        .label-row--ghost {
            min-height: 2.8rem;
            background: rgba(148, 163, 184, 0.03);
            border-radius: 0.5rem;
            border-bottom: none;
        }

        .label-row span {
            color: var(--shell-muted);
            font-size: 0.83rem;
            font-weight: 600;
            line-height: 1.5;
            text-transform: none;
        }

        .label-row strong {
            display: block;
            color: var(--shell-ink);
            font-size: 0.96rem;
            line-height: 1.55;
            word-break: break-word;
            font-weight: 700;
        }

        .label-row--multi {
            display: block;
        }

        .label-row--multi span {
            display: block;
            margin-bottom: 0.35rem;
        }

        .label-row--multi strong {
            display: block;
            padding: 0.25rem 0;
        }

        .report-subsection {
            margin-top: 1.5rem;
            padding-top: 1.25rem;
            border-top: 1px solid var(--shell-line);
        }

        .report-section-head {
            display: flex;
            justify-content: space-between;
            align-items: start;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .report-section-head.compact {
            align-items: center;
        }

        .mini-table-wrap {
            overflow: auto;
            border: 1px solid var(--shell-line);
            border-radius: var(--shell-radius-lg);
            background: rgba(255, 255, 255, 0.92);
            margin-top: 0.9rem;
        }

        .mini-table {
            width: 100%;
            min-width: 720px;
            border-collapse: collapse;
            font-size: 0.9rem;
        }

        .mini-table th,
        .mini-table td {
            padding: 1rem 1.05rem;
            border-bottom: 1px solid rgba(23, 49, 61, 0.08);
            text-align: left;
            vertical-align: top;
        }

        .mini-table th {
            background: #f8fafc;
            color: var(--shell-muted);
            font-size: 0.75rem;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .mini-table td:first-child {
            font-weight: 700;
            color: var(--shell-ink);
        }

        .pill-warning {
            background: rgba(183, 121, 31, 0.14);
            color: #8d5e12;
        }

        @media (max-width: 1100px) {
            .clinical-layout {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 640px) {
            .report-section-title {
                align-items: flex-start;
                flex-direction: column;
            }

            .report-section-title h3 {
                font-size: 1.15rem;
            }

            .clinical-panel {
                padding: 0.75rem;
                gap: 0.2rem;
            }

            .label-row {
                grid-template-columns: 1fr;
                gap: 0.15rem;
                padding: 0.4rem 0;
            }

            .label-row span {
                font-size: 0.75rem;
            }

            .label-row strong {
                font-size: 0.88rem;
            }

            .report-section-head {
                align-items: flex-start;
                flex-direction: column;
            }

            .mini-table {
                min-width: 620px;
            }
        }
    </style>
@endsection
