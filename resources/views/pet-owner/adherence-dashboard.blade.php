@extends('layouts.app')

@section('title', 'Medication Adherence - Dashboard')

@section('sidebar')
    <a href="{{ route('pet-owner.dashboard') }}" class="sidebar-item">Dashboard</a>
    <a href="{{ route('pet-owner.pets') }}" class="sidebar-item">My Pets</a>
    <a href="{{ route('pet-owner.appointments') }}" class="sidebar-item">Appointments</a>
    <a href="{{ route('pet-owner.teleconsultation') }}" class="sidebar-item">Teleconsultation</a>
    <a href="{{ route('pet-owner.prescriptions') }}" class="sidebar-item">Prescriptions</a>
    <a href="{{ route('adherence.dashboard') }}" class="sidebar-item active">Medication Adherence</a>
    <a href="{{ route('pet-owner.notifications') }}" class="sidebar-item">Notifications</a>
    <a href="#" class="sidebar-item" onclick="openPetCareAI(event)">Ask Pet Care AI</a>
@endsection

@section('content')
    <section class="hero-card">
        <div class="hero-row">
            <div>
                <span class="eyebrow">Medication Management</span>
                <h1 class="hero-title">Adherence Dashboard</h1>
                <p class="hero-copy">
                    Track your pet's medication intake and manage reminders in one place. Confirm each dose within the 3-hour window after its scheduled Philippine-time reminder.
                </p>
            </div>
            <div class="action-row">
                <a href="{{ route('pet-owner.prescriptions') }}" class="btn btn-secondary">← Back to Prescriptions</a>
            </div>
        </div>
    </section>

    <section class="workspace-grid" style="gap: 1.5rem;">
        <!-- Stats Overview -->
        <div class="grid grid-cols-3 gap-4 mb-4">
            <div class="card bg-blue-50 border border-blue-200">
                <div class="text-center p-6">
                    <div class="text-3xl font-bold text-blue-600 mb-2">{{ $pendingNotifications }}</div>
                    <div class="text-sm text-blue-900 font-medium">Pending Confirmations</div>
                    <p class="text-xs text-blue-700 mt-1">Require attention today</p>
                </div>
            </div>
            <div class="card bg-red-50 border border-red-200">
                <div class="text-center p-6">
                    <div class="text-3xl font-bold text-red-600 mb-2">{{ $recentMissed }}</div>
                    <div class="text-sm text-red-900 font-medium">Missed Last 30 Days</div>
                    <p class="text-xs text-red-700 mt-1">Track and improve</p>
                </div>
            </div>
            <div class="card bg-green-50 border border-green-200">
                <div class="text-center p-6">
                    <div class="text-3xl font-bold text-green-600 mb-2">{{ $confirmationRate }}%</div>
                    <div class="text-sm text-green-900 font-medium">Confirmation Rate</div>
                    <p class="text-xs text-green-700 mt-1">Last 30 days average</p>
                </div>
            </div>
        </div>

        <!-- Main Content Area -->
        <div class="grid grid-cols-12 gap-4">
            <!-- Notification Inbox (spans 8 columns) -->
            <div class="col-span-8">
                @livewire('adherence-notification-inbox')
            </div>

            <!-- Sidebar Info (spans 4 columns) -->
            <aside class="col-span-4 space-y-4">
                <!-- Tips Card -->
                <div class="card stack bg-amber-50 border border-amber-200">
                    <div class="surface-header">
                        <span class="eyebrow">Tips for Success</span>
                        <h3>Improve Your Adherence</h3>
                    </div>
                    <ul class="space-y-2 text-sm">
                        <li class="flex gap-2">
                            <span class="text-lg">Alarm Clock</span>
                            <span><strong>Set a routine:</strong> Take medications at the same time daily</span>
                        </li>
                        <li class="flex gap-2">
                            <span class="text-lg">Phone</span>
                            <span><strong>Use reminders:</strong> Enable notifications on your phone</span>
                        </li>
                        <li class="flex gap-2">
                            <span class="text-lg">Note</span>
                            <span><strong>Keep notes:</strong> Document any issues or side effects</span>
                        </li>
                        <li class="flex gap-2">
                            <span class="text-lg">Phone</span>
                            <span><strong>Contact support:</strong> Reach out if you have questions</span>
                        </li>
                    </ul>
                </div>

                <!-- Adherence Info Card -->
                <div class="card stack bg-blue-50 border border-blue-200">
                    <div class="surface-header">
                        <span class="eyebrow">Information - How It Works</span>
                        <h3>Confirmation Window</h3>
                    </div>
                    <div class="space-y-3 text-sm">
                        <p>
                            <strong class="text-blue-900">3 Hour Window:</strong>
                            You have 3 hours from the scheduled medication time to confirm your pet took the dose.
                        </p>
                        <p>
                            <strong class="text-blue-900">Auto-Missed:</strong>
                            If you don't confirm within the window, it's automatically marked as missed.
                        </p>
                        <p>
                            <strong class="text-blue-900">Philippine Time:</strong>
                            Daily schedules start from 6:00 AM and follow the prescribed dose count using Asia/Manila time.
                        </p>
                        <p>
                            <strong class="text-blue-900">Delete Option:</strong>
                            You can delete notification records from your inbox anytime.
                        </p>
                    </div>
                </div>
            </aside>
        </div>
    </section>

    <!-- Adherence Reminder Popup -->
    @livewire('adherence-reminder')

    <style>
        .grid {
            display: grid;
        }

        .grid-cols-3 {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .grid-cols-12 {
            grid-template-columns: repeat(12, minmax(0, 1fr));
        }

        .col-span-8 {
            grid-column: span 8 / span 8;
        }

        .col-span-4 {
            grid-column: span 4 / span 4;
        }

        @media (max-width: 1024px) {
            .col-span-8, .col-span-4 {
                grid-column: span 12 / span 12;
            }
        }
    </style>
@endsection
