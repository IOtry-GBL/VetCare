@extends('layouts.app')

@section('title', 'Prescription Report')

@section('sidebar')
    @include('staff.sidebar')
@endsection

@section('content')
    <section class="hero-card">
        <div class="hero-row">
            <div>
                <h1 class="hero-title">Prescription Report</h1>
                <p class="hero-copy">
                    Track issued medications and summarize adherence using the current intake-status data recorded in the system.
                </p>
            </div>

            <div class="action-row">
                <a href="{{ route('staff.reports') }}" class="btn btn-secondary">Back To Reports</a>
            </div>
        </div>
    </section>

    <section class="card">
        <div class="surface-header">
            <div>
                <h2>Filters</h2>
                <p class="section-copy">Search by medication name and limit the report to a specific issue window.</p>
            </div>
        </div>

        <form method="GET" action="{{ route('staff.reports.prescriptions') }}" class="form-grid">
            <div class="field">
                <label class="field-label" for="from_date">From</label>
                <input id="from_date" name="from_date" type="date" class="field-control" value="{{ $fromDate }}">
            </div>

            <div class="field">
                <label class="field-label" for="to_date">To</label>
                <input id="to_date" name="to_date" type="date" class="field-control" value="{{ $toDate }}">
            </div>

            <div class="field">
                <label class="field-label" for="medication">Medication</label>
                <input id="medication" name="medication" type="text" class="field-control" value="{{ request('medication') }}" placeholder="Cetirizine">
            </div>

            <div class="action-row" style="align-items: end;">
                <button type="submit" class="btn btn-primary">Apply Filters</button>
                <a href="{{ route('staff.reports.prescriptions') }}" class="btn btn-secondary">Reset</a>
            </div>
        </form>
    </section>

    <section class="card">
        <div class="metric-grid">
            <div class="metric-card">
                <span class="metric-label">Total</span>
                <strong class="metric-value">{{ $stats['total'] }}</strong>
            </div>
            <div class="metric-card">
                <span class="metric-label">High Compliance</span>
                <strong class="metric-value">{{ $stats['high_compliance'] }}</strong>
            </div>
            <div class="metric-card">
                <span class="metric-label">Low Compliance</span>
                <strong class="metric-value">{{ $stats['low_compliance'] }}</strong>
            </div>
        </div>
    </section>

    <section class="card">
        <div class="surface-header">
            <div>
                <h2>Issued Prescriptions</h2>
                <p class="section-copy">Compliance below is computed from `Taken` adherence logs versus all logs attached to the prescription.</p>
            </div>
        </div>

        <div class="table-wrap">
            <table class="app-table">
                <thead>
                    <tr>
                        <th>Issued</th>
                        <th>Medication</th>
                        <th>Patient</th>
                        <th>Owner</th>
                        <th>Veterinarian</th>
                        <th>Compliance</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($prescriptions as $prescription)
                        <tr>
                            <td>{{ $prescription->issued_at?->format('M j, Y g:i A') ?? 'Unknown date' }}</td>
                            <td>{{ $prescription->medication_name }}<br><span class="muted-copy">{{ $prescription->dosage }} / {{ $prescription->frequency }} / {{ $prescription->duration }}</span></td>
                            <td>{{ $prescription->medicalRecord?->pet?->name ?? 'Unknown Pet' }}</td>
                            <td>{{ $prescription->medicalRecord?->pet?->owner?->name ?? 'Unknown Owner' }}</td>
                            <td>{{ $prescription->medicalRecord?->consultation?->veterinarian?->name ? 'Dr. '.$prescription->medicalRecord->consultation->veterinarian->name : 'Not assigned' }}</td>
                            <td>{{ round($prescription->compliance_rate) }}%</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="muted-copy" style="text-align: center;">No prescriptions matched the current filters.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    @if ($prescriptions->hasPages())
        <section class="card">
            {{ $prescriptions->withQueryString()->links() }}
        </section>
    @endif
@endsection
