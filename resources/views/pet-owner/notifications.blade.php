@extends('layouts.app')

@section('sidebar')
    <a href="{{ route('pet-owner.dashboard') }}" class="sidebar-item"> Dashboard</a>
    <a href="{{ route('pet-owner.pets') }}" class="sidebar-item"> My Pets</a>
    <a href="{{ route('pet-owner.appointments') }}" class="sidebar-item"> Appointments</a>

    <a href="{{ route('pet-owner.prescriptions') }}" class="sidebar-item"> Prescriptions</a>
    <a href="{{ route('pet-owner.notifications') }}" class="sidebar-item active"> Notifications</a>
    <a href="#" class="sidebar-item" onclick="openPetCareAI(event)">Ask Pet Care AI</a>
@endsection

@section('content')
    <div class="card">
        <h1>Notifications Center</h1>
        <p>Stay updated with medication reminders, appointments, and vet messages.</p>
    </div>

    <div style="display: grid; gap: 1rem;">
        @forelse($notifications as $notification)
            @php
                $bgColor = $notification->is_read ? '#f3f4f6' : '#dbeafe';
                $borderColor = $notification->is_read ? '#d1d5db' : '#667eea';
                $opacity = $notification->is_read ? 0.7 : 1;
            @endphp
            <div style="padding: 1rem; background: {{ $bgColor }}; border-radius: 0.375rem; border-left: 4px solid {{ $borderColor }}; opacity: {{ $opacity }};">
                <div style="display: flex; justify-content: space-between; align-items: start;">
                    <div>
                        <p style="margin: 0; color: #1f2937;">{{ $notification->message }}</p>
                        <p style="margin: 0.5rem 0 0 0; font-size: 0.875rem; color: #6b7280;">{{ $notification->created_at->diffForHumans() }}</p>
                    </div>
                    <button class="btn btn-secondary" style="font-size: 0.875rem; padding: 0.5rem 1rem;">{{ $notification->is_read ? 'Dismissed' : 'Dismiss' }}</button>
                </div>
            </div>
        @empty
            <div style="padding: 2rem; text-align: center; background: #f9fafb; border-radius: 0.375rem;">
                <p style="color: #6b7280;">No notifications yet. You're all caught up!</p>
            </div>
        @endforelse
    </div>

    <div class="card" style="margin-top: 2rem;">
        <h2>Notification Preferences</h2>
        <div style="display: grid; gap: 1rem;">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <label style="display: flex; align-items: center; cursor: pointer;">
                    <input type="checkbox" checked style="margin-right: 0.5rem; width: 18px; height: 18px;">
                    <span>Medication Reminders</span>
                </label>
            </div>
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <label style="display: flex; align-items: center; cursor: pointer;">
                    <input type="checkbox" checked style="margin-right: 0.5rem; width: 18px; height: 18px;">
                    <span>Appointment Alerts</span>
                </label>
            </div>
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <label style="display: flex; align-items: center; cursor: pointer;">
                    <input type="checkbox" checked style="margin-right: 0.5rem; width: 18px; height: 18px;">
                    <span>Vet Messages</span>
                </label>
            </div>
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <label style="display: flex; align-items: center; cursor: pointer;">
                    <input type="checkbox" style="margin-right: 0.5rem; width: 18px; height: 18px;">
                    <span>Marketing & Promotions</span>
                </label>
            </div>
        </div>
        <button class="btn btn-primary" style="margin-top: 1.5rem;">Save Preferences</button>
    </div>
@endsection
