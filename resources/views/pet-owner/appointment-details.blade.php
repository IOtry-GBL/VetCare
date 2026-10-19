@extends('layouts.app')

@section('title', 'Appointment Details')

@section('sidebar')
    <a href="{{ route('pet-owner.dashboard') }}" class="sidebar-item">Dashboard</a>
    <a href="{{ route('pet-owner.pets') }}" class="sidebar-item">My Pets</a>
    <a href="{{ route('pet-owner.appointments') }}" class="sidebar-item active">Appointments</a>
    <a href="{{ route('pet-owner.prescriptions') }}" class="sidebar-item">Prescriptions</a>
    <a href="{{ route('pet-owner.notifications') }}" class="sidebar-item">Notifications</a>
@endsection

@section('content')
    @php
        $canManage = in_array($appointment->status, ['Pending', 'Approved'], true);
        $statusDisplay = match(strtolower($appointment->status)) {
            'pending' => 'Pending Review',
            'approved' => 'Confirmed',
            'rescheduled' => 'Rescheduled',
            default => $appointment->status,
        };
    @endphp

    <section class="hero-card">
        <h1 class="hero-title">Appointment Details</h1>
        <p class="hero-copy">{{ $appointment->pet->name }}'s appointment information</p>
    </section>

    <section class="card" style="max-width: 720px;">
        <div style="display: grid; gap: 1rem;">
            <div><strong>Pet Name</strong><p>{{ $appointment->pet->name }}</p></div>
            <div><strong>Schedule</strong><p>{{ $appointment->appointment_date->format('M d, Y \a\t h:i A') }}</p></div>
            <div><strong>Reason</strong><p>{{ $appointment->reason ?? 'N/A' }}</p></div>
            <div><strong>Veterinarian</strong><p>{{ $appointment->consultation?->veterinarian ? 'Dr. ' . $appointment->consultation->veterinarian->name : 'Awaiting assignment' }}</p></div>
            <div><strong>Status</strong><p>{{ $statusDisplay }}</p></div>
        </div>

        @if($canManage)
            <div style="display: flex; flex-wrap: wrap; gap: 0.75rem; margin-top: 2rem; padding-top: 1.25rem; border-top: 1px solid #e5e7eb;">
                <button type="button" class="btn btn-primary" onclick="document.getElementById('reschedule-modal').style.display = 'flex';">Reschedule</button>
                <form method="POST" action="{{ route('pet-owner.appointments.cancel', $appointment->id) }}">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn" style="background: #ef4444; color: white;" onclick="return confirm('Are you sure you want to cancel this appointment?')">Cancel Appointment</button>
                </form>
                <a href="{{ route('pet-owner.appointments') }}" class="btn btn-secondary">Back to Appointments</a>
            </div>
        @endif
    </section>

    @if($canManage)
        <div id="reschedule-modal" style="display: none; position: fixed; inset: 0; z-index: 1000; align-items: center; justify-content: center; background: rgba(0,0,0,0.5); padding: 1rem;">
            <div style="background: white; padding: 2rem; border-radius: 0.5rem; width: min(100%, 900px); max-height: 90vh; overflow-y: auto;">
                <h2>Reschedule Appointment</h2>
                <form method="POST" action="{{ route('pet-owner.appointments.reschedule', $appointment->id) }}">
                    @csrf
                    @method('PUT')
                    @include('components.appointment-calendar', [
                        'calendarId' => 'owner-reschedule-calendar-' . $appointment->id,
                        'viewer' => 'client',
                        'mode' => 'reschedule',
                        'sideByUside' => true,
                        'bookings' => $calendarBookings,
                        'slots' => $calendarSlots,
                        'ignoreAppointmentId' => $appointment->appointment_id,
                        'selectedDate' => $appointment->appointment_date->format('Y-m-d'),
                        'selectedTime' => $appointment->appointment_time->format('H:i'),
                        'reason' => $appointment->reason_for_visit,
                    ])
                    <div style="display: flex; gap: 0.75rem; justify-content: flex-end; margin-top: 1rem;">
                        <button type="button" class="btn btn-secondary" onclick="document.getElementById('reschedule-modal').style.display = 'none';">Close</button>
                        <button type="submit" class="btn btn-primary">Reschedule</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
@endsection
