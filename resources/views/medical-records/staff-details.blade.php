@extends('layouts.app')

@section('title', 'Medical Record Report')

@section('sidebar')
    @include('staff.sidebar')
@endsection

@section('content')
    @php
        $pet = $medicalRecord->pet;
        $owner = $pet?->owner;
        $consultation = $medicalRecord->consultation;
        $veterinarian = $consultation?->veterinarian;
        $appointment = $consultation?->appointment;
        $logs = $medicalRecord->prescriptions->flatMap->adherenceLogs;
        $taken = $logs->where('intake_status', 'Taken')->count();
        $total = $logs->count();
        $adherence = $total > 0 ? round(($taken / $total) * 100) : null;
        $date = $consultation?->consultation_date ?? $appointment?->appointment_date;
        $weight = $medicalRecord->weight !== null ? $medicalRecord->weight.' kg' : 'Not recorded';
        $temp = $medicalRecord->temp !== null ? $medicalRecord->temp.'°C' : 'Not recorded';
        $fee = $medicalRecord->fee !== null ? '₱'.number_format($medicalRecord->fee, 2) : 'Not recorded';
    @endphp

    <section class="hero-card"><div class="hero-row"><div><h1 class="hero-title">Record No. {{ $medicalRecord->record_id }}</h1><p class="hero-copy">Medical report for {{ $pet?->name ?? 'Unknown Pet' }}.</p></div><div class="action-row"><a href="{{ route('staff.medical-records') }}" class="btn btn-secondary">Back To Record List</a></div></div></section>

    <article class="card medical-report">
        <div class="report-section-title"><div><span class="eyebrow">Clinical Report</span><h3>{{ $appointment?->reason_for_visit ?: 'Medical record details' }}</h3></div><span class="report-stamp">Record #{{ $medicalRecord->record_id }}</span></div>

        <div class="clinical-layout">
            <div class="clinical-panel">
                <div class="label-row"><span>Family Name:</span><strong>{{ $owner?->name ?? 'Not on record' }}</strong></div>
                <div class="label-row"><span>Address:</span><strong>{{ $owner?->address ?? 'Not on record' }}</strong></div>
                <div class="label-row"><span>Species:</span><strong>{{ $pet?->species ?? 'N/A' }}</strong></div>
                <div class="label-row"><span>Breed:</span><strong>{{ $pet?->breed ?? 'N/A' }}</strong></div>
                <div class="label-row"><span>Birthday:</span><strong>{{ $pet?->date_of_birth?->format('M d, Y') ?? 'N/A' }}</strong></div>
                <div class="label-row"><span>Age:</span><strong>{{ $pet?->age_display ?? ($pet?->age !== null ? $pet->age.' years' : 'N/A') }}</strong></div>
                <div class="label-row"><span>Sex:</span><strong>{{ $pet?->sex ?? 'N/A' }}</strong></div>
                <div class="label-row"><span>Food:</span><strong>{{ $medicalRecord->food ?: 'Not recorded' }}</strong></div>
                <div class="label-row"><span>Environment:</span><strong>{{ $medicalRecord->environment ?: 'Not recorded' }}</strong></div>
                <div class="label-row label-row--multi"><span>Reason for Visit:</span><strong>{{ $appointment?->reason_for_visit ?: 'Not recorded' }}</strong></div>
                <div class="label-row label-row--multi"><span>Diagnosis:</span><strong>{{ $medicalRecord->diagnosis ?: 'No diagnosis recorded.' }}</strong></div>
                <div class="label-row label-row--multi"><span>Line of Treatment:</span><strong>{{ $medicalRecord->treatment_plan ?: 'No treatment plan recorded.' }}</strong></div>
                <div class="label-row"><span>Attending Vet:</span><strong>{{ $veterinarian?->name ? 'Dr. '.$veterinarian->name : 'Not assigned' }}</strong></div>
                <div class="label-row"><span>Fee:</span><strong>{{ $fee }}</strong></div>
                <div class="label-row"><span>Follow-up:</span><strong>{{ $medicalRecord->follow_up_date?->format('M d, Y') ?? 'Not scheduled' }}</strong></div>
            </div>
            <div class="clinical-panel clinical-panel--secondary">
                <div class="label-row"><span>Visit type:</span><strong>{{ $appointment?->consultation_mode ?? 'In-clinic' }}</strong></div>
                <div class="label-row"><span>Date:</span><strong>{{ $date?->format('M d, Y') ?? 'N/A' }}</strong></div>
                <div class="label-row"><span>Cell#:</span><strong>{{ $owner?->phone ?? $owner?->contact_no ?? 'N/A' }}</strong></div>
                <div class="label-row"><span>Weight:</span><strong>{{ $weight }}</strong></div>
                <div class="label-row"><span>Temp:</span><strong>{{ $temp }}</strong></div>
                <div class="label-row"><span>Vaccination Notes:</span><strong>{{ $medicalRecord->vaccination_notes ?: 'Not recorded' }}</strong></div>
                <div class="label-row label-row--multi"><span>Consultation Notes:</span><strong>{{ $consultation?->consultation_notes ?: 'Not recorded' }}</strong></div>
            </div>
        </div>

        <div class="report-subsection"><div class="report-section-head"><div><span class="detail-label">Prescription Records And Adherence Percentage</span><p class="section-copy">Medication linked to this medical record.</p></div><span class="pill {{ $medicalRecord->prescriptions->count() ? 'pill-success' : 'pill-neutral' }}">{{ $medicalRecord->prescriptions->count() }} item{{ $medicalRecord->prescriptions->count() === 1 ? '' : 's' }}</span></div>
            @if ($medicalRecord->prescriptions->isNotEmpty())
                <div class="mini-table-wrap"><table class="mini-table"><thead><tr><th>Medication</th><th>Dosage</th><th>Schedule</th><th>Issued</th><th>Adherence</th><th>Action</th></tr></thead><tbody>
                    @foreach ($medicalRecord->prescriptions as $prescription)
                        @php $doseTotal = $prescription->adherenceLogs->count(); $doseTaken = $prescription->adherenceLogs->where('intake_status', 'Taken')->count(); $rate = $doseTotal ? round(($doseTaken / $doseTotal) * 100) : null; @endphp
                        <tr><td>{{ $prescription->medication_name }}</td><td>{{ $prescription->dosage }}</td><td>{{ $prescription->frequency }} / {{ $prescription->duration }}</td><td>{{ $prescription->issued_at?->format('M j, Y') ?? 'Not recorded' }}</td><td>{{ $rate !== null ? $rate.'%' : 'No data' }}</td><td><a href="{{ route('staff.prescriptions.details', $prescription->prescription_id) }}" class="btn btn-sm btn-primary">View</a></td></tr>
                    @endforeach
                </tbody></table></div>
            @else <div class="empty-state">No prescriptions are linked to this medical record.</div> @endif
        </div>

        <div class="report-subsection"><div class="report-section-head"><div><span class="detail-label">Medical Adherence Record</span><p class="section-copy">Overall dose adherence for this medical record.</p></div><span class="pill {{ $adherence !== null && $adherence >= 80 ? 'pill-success' : 'pill-warning' }}">{{ $adherence !== null ? $adherence.'%' : 'No data' }}</span></div><div class="adherence-report-grid"><div class="report-field"><span class="summary-label">Taken</span><strong>{{ $taken }}</strong></div><div class="report-field"><span class="summary-label">Total Amount To Be Taken</span><strong>{{ $total }}</strong></div><div class="report-field"><span class="summary-label">Percentage</span><strong>{{ $adherence !== null ? $adherence.'%' : 'No data' }}</strong></div></div></div>
    </article>
@endsection

@section('styles')
<style>
.medical-report{background:rgba(255,255,255,.94)}.report-section-title,.report-section-head{display:flex;align-items:center;justify-content:space-between;gap:1rem}.report-section-title{padding-bottom:1rem;margin-bottom:1rem;border-bottom:1px solid var(--shell-line)}.report-section-title h3{margin:.15rem 0 0;font-size:1.45rem;color:var(--shell-ink)}.report-stamp{display:inline-flex;align-items:center;min-height:34px;padding:.35rem .8rem;border-radius:999px;background:rgba(15,139,141,.12);color:var(--shell-accent-deep);font-size:.8rem;font-weight:700}.clinical-layout{display:grid;grid-template-columns:minmax(0,1.15fr) minmax(240px,.85fr);gap:1.25rem;margin-top:.75rem}.clinical-panel{display:flex;flex-direction:column;gap:.5rem;padding:1rem 1.1rem;border:1px solid var(--shell-line);border-radius:var(--shell-radius-lg);background:rgba(248,250,252,.8)}.clinical-panel--secondary{background:linear-gradient(180deg,rgba(248,250,252,.9),rgba(255,255,255,.8))}.label-row{display:grid;grid-template-columns:minmax(120px,150px) minmax(0,1fr);gap:.85rem;align-items:flex-start;padding:.55rem 0;border-bottom:1px solid rgba(15,23,42,.06)}.label-row span{color:var(--shell-muted);font-size:.83rem;font-weight:600;line-height:1.5}.label-row strong{display:block;color:var(--shell-ink);font-size:.96rem;line-height:1.55;word-break:break-word}.label-row--multi{display:block}.label-row--multi span{display:block;margin-bottom:.35rem}.report-subsection{margin-top:1.5rem;padding-top:1.25rem;border-top:1px solid var(--shell-line)}.mini-table-wrap{overflow:auto;border:1px solid var(--shell-line);border-radius:var(--shell-radius-lg);margin-top:.9rem}.mini-table{width:100%;min-width:720px;border-collapse:collapse;font-size:.9rem}.mini-table th,.mini-table td{padding:1rem 1.05rem;border-bottom:1px solid rgba(23,49,61,.08);text-align:left;vertical-align:top}.mini-table th{background:#f8fafc;color:var(--shell-muted);font-size:.75rem;text-transform:uppercase}.pill-warning{background:rgba(183,121,31,.14);color:#8d5e12}@media(max-width:1100px){.clinical-layout{grid-template-columns:1fr}}@media(max-width:640px){.report-section-title,.report-section-head{align-items:flex-start;flex-direction:column}.label-row{grid-template-columns:1fr;gap:.15rem}}
</style>
@endsection
