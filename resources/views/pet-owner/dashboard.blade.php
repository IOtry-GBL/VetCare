@extends('layouts.app')

@section('sidebar')
    <a href="{{ route('pet-owner.dashboard') }}" class="sidebar-item active"> Dashboard</a>
    <a href="{{ route('pet-owner.pets') }}" class="sidebar-item"> My Pets</a>
    <a href="{{ route('pet-owner.appointments') }}" class="sidebar-item"> Appointments</a>

    <a href="{{ route('pet-owner.prescriptions') }}" class="sidebar-item"> Prescriptions</a>
    <a href="{{ route('pet-owner.notifications') }}" class="sidebar-item"> Notifications</a>
    <a href="#" class="sidebar-item" onclick="openPetCareAI(event)">Ask Pet Care AI</a>
@endsection

@section('content')
    <div class="card">
        <h1>Welcome, {{ auth()->user()->name }}!</h1>
        <p>Here's what's happening with your pets today.</p>
    </div>

    <div class="grid">
        <div class="widget">
            <div class="widget-title">Upcoming Appointments</div>
            <div class="widget-value">{{ $upcomingAppointments->count() }}</div>
            <p style="color: #6b7280; margin-top: 0.5rem;">
                @if($upcomingAppointments->count() > 0)
                    Next: {{ $upcomingAppointments->first()->appointment_date->diffForHumans() }}
                @else
                    No upcoming appointments
                @endif
            </p>
        </div>

        <div class="widget">
            <div class="widget-title">Total Pets</div>
            <div class="widget-value">{{ $pets->count() }}</div>
            <p style="color: #6b7280; margin-top: 0.5rem;">Active pets in system</p>
        </div>

        <div class="widget">
            <div class="widget-title">Active Prescriptions</div>
            <div class="widget-value">{{ $prescriptions->count() }}</div>
            <p style="color: #6b7280; margin-top: 0.5rem;">Medications to track</p>
        </div>

        <div class="widget">
            <div class="widget-title">Health Status</div>
            <div class="widget-value" style="color: var(--color-monitor);">OK</div>
            <p style="color: #6b7280; margin-top: 0.5rem;">All pets healthy</p>
        </div>
    </div>

    <div class="card">
        <h2>Your Pets ({{ $pets->count() }})</h2>
        <div style="display: grid; gap: 1rem;">
            @forelse($pets as $pet)
                <div style="padding: 1rem; background: #f9fafb; border-radius: 0.375rem; border-left: 4px solid #667eea;">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <strong>{{ $pet->name }}</strong> ({{ $pet->breed }}, {{ $pet->age }} years)
                            <p style="margin: 0.5rem 0 0 0; color: #6b7280;">{{ $pet->species }} • {{ $pet->weight }}kg</p>
                        </div>
                        <div style="text-align: right;">
                            <span class="badge badge-monitor">Active</span>
                        </div>
                    </div>
                </div>
            @empty
                <p style="color: #6b7280;">No pets registered yet.</p>
            @endforelse
        </div>
        <a href="{{ route('pet-owner.pets') }}" class="btn btn-primary" style="margin-top: 1rem;">View All Pets</a>
    </div>

    <div class="card">
        <h2>Quick Actions</h2>
        <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
            <a href="{{ route('pet-owner.symptom-checker') }}" class="btn btn-primary">Start AI Symptom Check</a>
            <a href="{{ route('pet-owner.appointments') }}" class="btn btn-secondary">Book Appointment</a>
        </div>
    </div>
@endsection
