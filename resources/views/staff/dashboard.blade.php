@extends('layouts.app')

@section('sidebar')
    @include('staff.sidebar')
@endsection

@section('content')
    <div class="card">
        <h1>Welcome, {{ auth()->user()->name }}!</h1>
        <p>Here's your daily clinic overview.</p>
    </div>

    <div class="grid">
        <div class="widget">
            <div class="widget-title">Today's Appointments</div>
            <div class="widget-value">{{ $todayAppointments }}</div>
            <p style="color: #6b7280; margin-top: 0.5rem;">Scheduled for today</p>
        </div>

        <div class="widget">
            <div class="widget-title">Pending Confirmations</div>
            <div class="widget-value" style="color: var(--color-visit);">{{ $pending }}</div>
            <p style="color: #6b7280; margin-top: 0.5rem;">Awaiting action</p>
        </div>

        <div class="widget">
            <div class="widget-title">Emergency Alerts</div>
            <div class="widget-value" style="color: var(--color-emergency);">{{ $emergencies }}</div>
            <p style="color: #6b7280; margin-top: 0.5rem;">High priority</p>
        </div>

        <div class="widget">
            <div class="widget-title">Total Appointments</div>
            <div class="widget-value">{{ $allAppointments }}</div>
            <p style="color: #6b7280; margin-top: 0.5rem;">In the system</p>
        </div>
    </div>

    <div class="card">
        <h2>Quick Tasks</h2>
        
        <!-- Emergency Appointments Section -->
        @if($emergencies > 0)
            <div id="section-emergency" style="margin-bottom: 2rem; border: 1px solid #e5e7eb; padding: 1rem; border-radius: 0.375rem;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                    <h3 style="color: #ef4444; margin: 0;">Emergency Appointments ({{ $emergencyAppointments->count() }})</h3>
                    <div style="display: flex; gap: 0.5rem;">
                        <button onclick="toggleSeeAll('emergency')" id="see-all-emergency" class="btn-action" style="padding: 0.5rem 1rem; background: #ef4444; color: white; border: none; border-radius: 0.375rem; cursor: pointer; font-size: 0.875rem;">See All</button>
                    </div>
                </div>
                <div id="content-emergency" style="display: grid; gap: 1rem;">
                    @forelse($emergencyAppointments as $appointment)
                        <div data-section="emergency" style="padding: 1rem; background: #fee2e2; border-radius: 0.375rem; border-left: 4px solid #ef4444;">
                            <div style="display: flex; justify-content: space-between; align-items: start;">
                                <div style="flex: 1;">
                                    <strong>{{ $appointment->pet->name }} ({{ $appointment->pet->species }})</strong>
                                    <p style="margin: 0.5rem 0 0 0; color: #6b7280;">Owner: {{ $appointment->pet->owner->name }}</p>
                                    <p style="margin: 0.25rem 0 0 0; font-size: 0.875rem;">Phone: {{ $appointment->pet->owner->phone ?? 'N/A' }}</p>
                                    <p style="margin: 0.5rem 0 0 0; font-size: 0.875rem;"><strong>Reason:</strong> {{ $appointment->reason }}</p>
                                    <p style="margin: 0.25rem 0 0 0; font-size: 0.875rem;">{{ $appointment->appointment_date ? $appointment->appointment_date->format('M d, Y • h:i A') : 'TBD' }}</p>
                                </div>
                                <div style="text-align: right;">
                                    <a href="{{ route('staff.appointments.confirm', $appointment->id) }}" class="btn btn-primary" style="padding: 0.5rem 0.75rem; font-size: 0.75rem; text-decoration: none; display: inline-block;">Review & Confirm</a>
                                </div>
                            </div>
                        </div>
                    @empty
                        <p style="color: #6b7280;">No emergency appointments</p>
                    @endforelse
                </div>
            </div>
        @endif

        <!-- Regular Check-ups Section -->
        @if($regularAppointments->count() > 0)
            <div id="section-regular" style="margin-bottom: 2rem; border: 1px solid #e5e7eb; padding: 1rem; border-radius: 0.375rem;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                    <h3 style="color: #10b981; margin: 0;">Regular Check-ups ({{ $regularAppointments->count() }})</h3>
                    <div style="display: flex; gap: 0.5rem;">
                        <button onclick="toggleSeeAll('regular')" id="see-all-regular" class="btn-action" style="padding: 0.5rem 1rem; background: #10b981; color: white; border: none; border-radius: 0.375rem; cursor: pointer; font-size: 0.875rem;">See All</button>
                    </div>
                </div>
                <div id="content-regular" style="display: grid; gap: 1rem;">
                    @forelse($regularAppointments as $appointment)
                        <div data-section="regular" style="padding: 1rem; background: #dcfce7; border-radius: 0.375rem; border-left: 4px solid #10b981;">
                            <div style="display: flex; justify-content: space-between; align-items: start;">
                                <div style="flex: 1;">
                                    <strong>{{ $appointment->pet->name }} ({{ $appointment->pet->species }})</strong>
                                    <p style="margin: 0.5rem 0 0 0; color: #6b7280;">Owner: {{ $appointment->pet->owner->name }}</p>
                                    <p style="margin: 0.25rem 0 0 0; font-size: 0.875rem;">Phone: {{ $appointment->pet->owner->phone ?? 'N/A' }}</p>
                                    <p style="margin: 0.5rem 0 0 0; font-size: 0.875rem;"><strong>Reason:</strong> {{ $appointment->reason }}</p>
                                    <p style="margin: 0.25rem 0 0 0; font-size: 0.875rem;">{{ $appointment->appointment_date ? $appointment->appointment_date->format('M d, Y • h:i A') : 'TBD' }}</p>
                                </div>
                                <div style="text-align: right;">
                                    <a href="{{ route('staff.appointments.confirm', $appointment->id) }}" class="btn btn-primary" style="padding: 0.5rem 0.75rem; font-size: 0.75rem; text-decoration: none; display: inline-block;">Review & Confirm</a>
                                </div>
                            </div>
                        </div>
                    @empty
                        <p style="color: #6b7280;">No regular appointments</p>
                    @endforelse
                </div>
            </div>
        @endif

        @if($emergencies == 0 && $regularAppointments->count() == 0)
            <div style="padding: 1rem; background: #f0fdf4; border-radius: 0.375rem; border-left: 4px solid #10b981;">
                <strong>No Pending Appointments</strong>
                <p style="margin: 0.5rem 0 0 0; color: #6b7280; font-size: 0.875rem;">All appointments are up to date</p>
            </div>
        @endif
    </div>

    <div class="card">
        <h2>Approved Appointments</h2>
        
        <!-- Upcoming Approved Appointments -->
        @if($upcomingApprovedAppointments->count() > 0)
            <div id="section-upcoming" style="margin-bottom: 2rem; border: 1px solid #e5e7eb; padding: 1rem; border-radius: 0.375rem;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                    <h3 style="color: #667eea; margin: 0;">Upcoming Appointments ({{ $upcomingApprovedAppointments->count() }})</h3>
                    <div style="display: flex; gap: 0.5rem;">
                        <button onclick="toggleSeeAll('upcoming')" id="see-all-upcoming" class="btn-action" style="padding: 0.5rem 1rem; background: #667eea; color: white; border: none; border-radius: 0.375rem; cursor: pointer; font-size: 0.875rem;">See All</button>
                    </div>
                </div>
                <div id="content-upcoming" style="display: grid; gap: 1rem; margin-bottom: 1rem;">
                @forelse($upcomingApprovedAppointments as $appointment)
                        <div data-section="upcoming" style="padding: 1rem; background: #ffffff; border-radius: 0.375rem; border-left: 4px solid #667eea; border: 1px solid #e5e7eb;">
                        <div style="display: flex; justify-content: space-between; align-items: start;">
                            <div style="flex: 1;">
                                <strong style="font-size: 1rem;">{{ $appointment->pet->name }} ({{ $appointment->pet->species }})</strong>
                                <p style="margin: 0.5rem 0 0 0; color: #6b7280;">Owner: {{ $appointment->pet->owner->name }}</p>
                                <p style="margin: 0.25rem 0 0 0; font-size: 0.875rem;">Phone: {{ $appointment->pet->owner->phone ?? 'N/A' }}</p>
                                <p style="margin: 0.5rem 0 0 0; font-size: 0.875rem;"><strong>Reason:</strong> {{ $appointment->reason }}</p>
                            </div>
                            <div style="text-align: right;">
                                <div style="padding: 0.75rem; background: #dbeafe; border-radius: 0.375rem; border-left: 3px solid #667eea;">
                                    <p style="margin: 0; font-size: 0.875rem; color: #667eea;"><strong>Date & Time</strong></p>
                                    <p style="margin: 0.5rem 0 0 0; font-size: 1rem; font-weight: bold; color: #1f2937;">{{ $appointment->appointment_date ? $appointment->appointment_date->format('M d, Y') : 'TBD' }}</p>
                                    <p style="margin: 0.25rem 0 0 0; font-size: 1rem; font-weight: bold; color: #667eea;">{{ $appointment->appointment_date ? $appointment->appointment_date->format('h:i A') : '' }}</p>
                                </div>
                                @if($appointment->veterinarian)
                                    <p style="margin: 1rem 0 0 0; font-size: 0.875rem; color: #6b7280;">Vet: <strong>{{ $appointment->veterinarian->name }}</strong></p>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <p style="color: #6b7280;">No upcoming appointments</p>
                @endforelse
                </div>
            </div>
        @endif
        
        <!-- Missed Approved Appointments -->
        @if($missedApprovedAppointments->count() > 0)
            <div id="section-missed" style="margin-bottom: 2rem; border: 1px solid #e5e7eb; padding: 1rem; border-radius: 0.375rem;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                    <h3 style="color: #ef4444; margin: 0;">Missed Appointments ({{ $missedApprovedAppointments->count() }})</h3>
                    <div style="display: flex; gap: 0.5rem;">
                        <button onclick="toggleSeeAll('missed')" id="see-all-missed" class="btn-action" style="padding: 0.5rem 1rem; background: #ef4444; color: white; border: none; border-radius: 0.375rem; cursor: pointer; font-size: 0.875rem;">See All</button>
                    </div>
                </div>
                <div id="content-missed" style="display: grid; gap: 1rem;">
                @forelse($missedApprovedAppointments as $appointment)
                        <div data-section="missed" style="padding: 1rem; background: #fef2f2; border-radius: 0.375rem; border-left: 4px solid #ef4444; border: 1px solid #fecaca; opacity: 0.8;">
                        <div style="display: flex; justify-content: space-between; align-items: start;">
                            <div style="flex: 1;">
                                <strong style="font-size: 1rem; color: #991b1b;">{{ $appointment->pet->name }} ({{ $appointment->pet->species }})</strong>
                                <p style="margin: 0.5rem 0 0 0; color: #6b7280;">Owner: {{ $appointment->pet->owner->name }}</p>
                                <p style="margin: 0.25rem 0 0 0; font-size: 0.875rem;">Phone: {{ $appointment->pet->owner->phone ?? 'N/A' }}</p>
                                <p style="margin: 0.5rem 0 0 0; font-size: 0.875rem;"><strong>Reason:</strong> {{ $appointment->reason }}</p>
                            </div>
                            <div style="text-align: right;">
                                <div style="padding: 0.75rem; background: #fee2e2; border-radius: 0.375rem; border-left: 3px solid #ef4444;">
                                    <p style="margin: 0; font-size: 0.875rem; color: #ef4444;"><strong>Was On</strong></p>
                                    <p style="margin: 0.5rem 0 0 0; font-size: 1rem; font-weight: bold; color: #991b1b;">{{ $appointment->appointment_date ? $appointment->appointment_date->format('M d, Y') : 'TBD' }}</p>
                                    <p style="margin: 0.25rem 0 0 0; font-size: 1rem; font-weight: bold; color: #ef4444;">{{ $appointment->appointment_date ? $appointment->appointment_date->format('h:i A') : '' }}</p>
                                </div>
                                @if($appointment->veterinarian)
                                    <p style="margin: 1rem 0 0 0; font-size: 0.875rem; color: #6b7280;">Vet: <strong>{{ $appointment->veterinarian->name }}</strong></p>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <p style="color: #6b7280;">No missed appointments</p>
                @endforelse
                </div>
            </div>
        @endif
        
        @if($upcomingApprovedAppointments->count() == 0 && $missedApprovedAppointments->count() == 0)
            <div style="padding: 1rem; background: #f3f4f6; border-radius: 0.375rem; border-left: 4px solid #9ca3af;">
                <p style="color: #6b7280; margin: 0;">No scheduled appointments</p>
            </div>
        @endif
    </div>

    <div class="card">
        <h2>Recent Activity</h2>
        <div style="display: grid; gap: 0.75rem; font-size: 0.875rem;">
            <div>Dashboard loaded with {{ $allAppointments }} total appointment(s)</div>
            <div>{{ $pending }} appointment(s) pending confirmation</div>
            @if($emergencies > 0)
                <div>{{ $emergencies }} emergency appointment(s) require immediate attention</div>
            @endif
            <div>{{ $todayAppointments }} appointment(s) scheduled for today</div>
            @if($recentPending->count() > 0)
                <div>Most recent pending: {{ $recentPending->first()->pet->name ?? 'Unknown' }} - {{ $recentPending->first()->pet->owner->name ?? 'Unknown' }}</div>
            @endif
        </div>
    </div>

    <script>
        function toggleSeeAll(sectionId) {
            const content = document.getElementById(`content-${sectionId}`);
            const button = document.getElementById(`see-all-${sectionId}`);
            
            if (!content) return;
            
            const items = content.querySelectorAll('[data-section="' + sectionId + '"]');
            const hiddenItems = Array.from(items).filter(item => item.style.display === 'none');
            
            if (hiddenItems.length > 0) {
                // Show all
                hiddenItems.forEach(item => item.style.display = 'block');
                if (button) button.textContent = 'Show Less';
            } else {
                // Hide extras (keep first item visible)
                items.forEach((item, index) => {
                    if (index > 0) {
                        item.style.display = 'none';
                    }
                });
                if (button) button.textContent = 'See All';
            }
        }

        // Initialize: hide extra items on load
        document.addEventListener('DOMContentLoaded', function() {
            ['emergency', 'regular', 'upcoming', 'missed'].forEach(sectionId => {
                const content = document.getElementById(`content-${sectionId}`);
                if (content) {
                    const items = content.querySelectorAll('[data-section="' + sectionId + '"]');
                    items.forEach((item, index) => {
                        if (index > 0) {
                            item.style.display = 'none';
                        }
                    });
                }
            });
        });
    </script>
@endsection
