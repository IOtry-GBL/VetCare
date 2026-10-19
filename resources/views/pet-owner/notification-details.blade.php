@extends('layouts.app')

@section('title', 'Notification Details')

@section('sidebar')
    <a href="{{ route('pet-owner.dashboard') }}" class="sidebar-item">Dashboard</a>
    <a href="{{ route('pet-owner.pets') }}" class="sidebar-item">My Pets</a>
    <a href="{{ route('pet-owner.appointments') }}" class="sidebar-item">Appointments</a>
    <a href="{{ route('pet-owner.prescriptions') }}" class="sidebar-item">Prescriptions</a>
    <a href="{{ route('pet-owner.notifications') }}" class="sidebar-item active">Notifications</a>
@endsection

@section('content')
    <section class="hero-card">
        <h1 class="hero-title">Notification Details</h1>
        <p class="hero-copy">{{ $notification->display_type }} notification</p>
    </section>

    <section class="card" style="max-width: 720px;">
        <div style="display: grid; gap: 1rem;">
            <div><strong>Type</strong><p>{{ $notification->display_type }}</p></div>
            <div><strong>Date Sent</strong><p>{{ $notification->created_at->format('M d, Y h:i A') }}</p></div>
            <div><strong>Pet Name</strong><p>{{ $notification->pet_name }}</p></div>
            <div><strong>Message</strong><p>{{ $notification->display_message }}</p></div>
            @if($notification->display_type === 'Adherence')
                <div><strong>Scheduled Time</strong><p>{{ $notification->scheduled_at->format('M d, Y h:i A') }}</p></div>
                <div><strong>Medication</strong><p>{{ $notification->medication_name }} ({{ $notification->dosage }})</p></div>
            @else
                <div><strong>Status</strong><p>{{ $notification->notification_status }}</p></div>
            @endif
        </div>

        <div style="margin-top: 2rem;">
            <a href="{{ route('pet-owner.notifications') }}" class="btn btn-secondary">Back to Notifications</a>
        </div>
    </section>
@endsection
