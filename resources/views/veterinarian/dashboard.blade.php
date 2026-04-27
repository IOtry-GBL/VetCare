@extends('layouts.app')

@section('title', 'Veterinarian Dashboard')

@section('sidebar')
    <a href="{{ route('vet.dashboard') }}" class="sidebar-item active">Dashboard</a>
    <a href="{{ route('vet.appointments') }}" class="sidebar-item">Appointments</a>
    <a href="{{ route('vet.medical-records') }}" class="sidebar-item">Medical Records</a>
    <a href="{{ route('vet.prescriptions') }}" class="sidebar-item">E-Prescriptions</a>
    <a href="{{ route('vet.adherence-monitoring') }}" class="sidebar-item">Medication Adherence</a>
@endsection

@section('content')
    <div class="card">
        <h1>Welcome, {{ auth()->user()->name }}!</h1>
        <p>Track your cases, manage appointments, and monitor patient adherence.</p>
    </div>

    <div class="grid">
        <div class="widget">
            <div class="widget-title">Emergency Alerts</div>
            <div class="widget-value" style="color: var(--color-emergency);">{{ $emergencies->count() }}</div>
            <p style="color: #6b7280; margin-top: 0.5rem;">Urgent cases</p>
        </div>

        <div class="widget">
            <div class="widget-title">Triage Queue</div>
            <div class="widget-value" style="color: #f59e0b;">{{ $triage->count() }}</div>
            <p style="color: #6b7280; margin-top: 0.5rem;">High priority</p>
        </div>

        <div class="widget">
            <div class="widget-title">Medication Adherence</div>
            <div class="widget-value" style="color: #3b82f6;">{{ $adherenceRate }}%</div>
            <p style="color: #6b7280; margin-top: 0.5rem;">Across {{ $totalPrescriptions }} prescriptions</p>
        </div>
    </div>

    @if($emergencies->count() > 0)
        <div class="card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                <h2 style="margin: 0;">Emergency Cases ({{ $emergencies->count() }})</h2>
            </div>

            <div style="display: grid; gap: 1rem;">
                @foreach($emergencies->sortByDesc('created_at')->take(5) as $emergency)
                    <div style="padding: 1rem; background: #fee2e2; border-radius: 0.375rem; border-left: 4px solid #ef4444;">
                        <div style="display: flex; justify-content: space-between; align-items: start;">
                            <div style="flex: 1;">
                                <strong>{{ $emergency->pet->name ?? 'Patient' }}</strong>
                                <p style="margin: 0.5rem 0 0 0; color: #6b7280; font-size: 0.875rem;">Symptom: {{ $emergency->chief_symptom ?? 'No details recorded' }}</p>
                                <p style="margin: 0.25rem 0 0 0; color: #6b7280; font-size: 0.875rem;">Level: {{ $emergency->concern_level }}</p>
                            </div>
                            <div style="text-align: right;">
                                <a href="{{ route('vet.appointments') }}" class="btn btn-primary" style="padding: 0.5rem 0.75rem; font-size: 0.75rem; text-decoration: none; display: inline-block;">Review</a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <div class="card">
        <h2>Quick Links</h2>
        <div class="action-row">
            <a href="{{ route('vet.appointments') }}" class="btn btn-primary">Manage Appointments</a>
            <a href="{{ route('vet.medical-records') }}" class="btn btn-secondary">Medical Records</a>
            <a href="{{ route('vet.prescriptions') }}" class="btn btn-secondary">E-Prescriptions</a>
            <a href="{{ route('vet.adherence-monitoring') }}" class="btn btn-secondary">Medication Adherence</a>
        </div>
    </div>
@endsection