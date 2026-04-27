@extends('layouts.app')

@section('sidebar')
    @include('staff.sidebar')
@endsection

@section('content')
    <div class="card">
        <h1>Appointment Queue - Pending Review</h1>
        <p>All pending appointment requests. Confirm which ones are viable for scheduling.</p>
        
        <!-- Filter Buttons -->
        <div style="display: flex; gap: 0.5rem; margin-top: 1.5rem; flex-wrap: wrap;">
            <button onclick="filterQueue('all')" id="filter-all" style="padding: 0.5rem 1rem; background: #3b82f6; color: white; border: none; border-radius: 0.375rem; cursor: pointer; font-weight: 500;">Show All</button>
            <button onclick="filterQueue('emergency')" id="filter-emergency" style="padding: 0.5rem 1rem; background: #ef4444; color: white; border: none; border-radius: 0.375rem; cursor: pointer; font-weight: 500;">Emergency Only</button>
            <button onclick="filterQueue('high')" id="filter-high" style="padding: 0.5rem 1rem; background: #f59e0b; color: white; border: none; border-radius: 0.375rem; cursor: pointer; font-weight: 500;">High Priority Only</button>
            <button onclick="filterQueue('routine')" id="filter-routine" style="padding: 0.5rem 1rem; background: #10b981; color: white; border: none; border-radius: 0.375rem; cursor: pointer; font-weight: 500;">Routine Only</button>
        </div>
    </div>

    <div style="display: grid; gap: 1rem;" id="queue-container">
        <!-- Emergency Queue -->
        <div id="queue-emergency" class="queue-section" style="display: block;">
            <h2 style="color: #ef4444; margin-bottom: 1rem; cursor: pointer;" onclick="filterQueue('emergency')">Emergency Appointments ({{ $emergencyQueue->count() }})</h2>
            @forelse($emergencyQueue as $appointment)
                <div style="padding: 1rem; margin-bottom: 1rem; background: #fee2e2; border-radius: 0.375rem; border-left: 4px solid #ef4444;">
                    <div style="display: flex; justify-content: space-between; align-items: start;">
                        <div style="flex: 1;">
                            <strong>{{ $appointment->pet->name }} ({{ $appointment->pet->species }})</strong>
                            <p style="margin: 0.5rem 0 0 0; color: #6b7280;">Owner: {{ $appointment->pet->owner->name }}</p>
                            <p style="margin: 0.25rem 0 0 0; font-size: 0.875rem;">Phone: {{ $appointment->pet->owner->phone ?? 'N/A' }}</p>
                            <p style="margin: 0.5rem 0 0 0; font-size: 0.875rem;"><strong>Reason:</strong> {{ $appointment->reason }}</p>
                            <p style="margin: 0.25rem 0 0 0; font-size: 0.875rem;">{{ $appointment->appointment_date ? $appointment->appointment_date->format('M d, Y • h:i A') : 'TBD' }}</p>
                        </div>
                        <div style="text-align: right;">
                            <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                                <span style="display: inline-block; padding: 0.25rem 0.75rem; background: #ef4444; color: white; border-radius: 0.25rem; font-size: 0.75rem; font-weight: 500;">PENDING</span>
                                <a href="{{ route('staff.appointments.confirm', $appointment) }}" class="btn btn-primary" style="padding: 0.5rem 0.75rem; font-size: 0.75rem; text-decoration: none; display: inline-block;">Review & Confirm</a>
                                <form method="POST" action="{{ route('staff.appointments.reject', $appointment) }}" style="margin: 0;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" onclick="return confirm('Are you sure you want to reject this appointment?')" style="padding: 0.5rem 0.75rem; background: #dc2626; color: white; border: none; border-radius: 0.375rem; font-size: 0.75rem; cursor: pointer; width: 100%;">Reject</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div style="padding: 1rem; margin-bottom: 1rem; background: #fee2e2; border-radius: 0.375rem; border-left: 4px solid #ef4444;">
                    <p style="color: #991b1b; margin: 0;">No emergency appointments</p>
                </div>
            @endforelse
        </div>

        <!-- High Priority Queue -->
        <div id="queue-high" class="queue-section" style="display: block;">
            <h2 style="color: #f59e0b; margin-bottom: 1rem; cursor: pointer;" onclick="filterQueue('high')">High Priority Appointments ({{ $highPriorityQueue->count() }})</h2>
            @forelse($highPriorityQueue as $appointment)
                <div style="padding: 1rem; margin-bottom: 1rem; background: #fef3c7; border-radius: 0.375rem; border-left: 4px solid #f59e0b;">
                    <div style="display: flex; justify-content: space-between; align-items: start;">
                        <div style="flex: 1;">
                            <strong>{{ $appointment->pet->name }} ({{ $appointment->pet->species }})</strong>
                            <p style="margin: 0.5rem 0 0 0; color: #6b7280;">Owner: {{ $appointment->pet->owner->name }}</p>
                            <p style="margin: 0.25rem 0 0 0; font-size: 0.875rem;">Phone: {{ $appointment->pet->owner->phone ?? 'N/A' }}</p>
                            <p style="margin: 0.5rem 0 0 0; font-size: 0.875rem;"><strong>Reason:</strong> {{ $appointment->reason }}</p>
                            <p style="margin: 0.25rem 0 0 0; font-size: 0.875rem;">{{ $appointment->appointment_date ? $appointment->appointment_date->format('M d, Y • h:i A') : 'TBD' }}</p>
                        </div>
                        <div style="text-align: right;">
                            <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                                <span style="display: inline-block; padding: 0.25rem 0.75rem; background: #f59e0b; color: white; border-radius: 0.25rem; font-size: 0.75rem; font-weight: 500;">PENDING</span>
                                <a href="{{ route('staff.appointments.confirm', $appointment) }}" class="btn btn-primary" style="padding: 0.5rem 0.75rem; font-size: 0.75rem; text-decoration: none; display: inline-block;">Review & Confirm</a>
                                <form method="POST" action="{{ route('staff.appointments.reject', $appointment) }}" style="margin: 0;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" onclick="return confirm('Are you sure you want to reject this appointment?')" style="padding: 0.5rem 0.75rem; background: #dc2626; color: white; border: none; border-radius: 0.375rem; font-size: 0.75rem; cursor: pointer; width: 100%;">Reject</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div style="padding: 1rem; margin-bottom: 1rem; background: #fef3c7; border-radius: 0.375rem; border-left: 4px solid #f59e0b;">
                    <p style="color: #78350f; margin: 0;">No high priority appointments</p>
                </div>
            @endforelse
        </div>

        <!-- Routine Queue -->
        <div id="queue-routine" class="queue-section" style="display: block;">
            <h2 style="color: #10b981; margin-bottom: 1rem; cursor: pointer;" onclick="filterQueue('routine')">Routine Appointments ({{ $routineQueue->count() }})</h2>
            @forelse($routineQueue as $appointment)
                <div style="padding: 1rem; margin-bottom: 1rem; background: #dcfce7; border-radius: 0.375rem; border-left: 4px solid #10b981;">
                    <div style="display: flex; justify-content: space-between; align-items: start;">
                        <div style="flex: 1;">
                            <strong>{{ $appointment->pet->name }} ({{ $appointment->pet->species }})</strong>
                            <p style="margin: 0.5rem 0 0 0; color: #6b7280;">Owner: {{ $appointment->pet->owner->name }}</p>
                            <p style="margin: 0.25rem 0 0 0; font-size: 0.875rem;">Phone: {{ $appointment->pet->owner->phone ?? 'N/A' }}</p>
                            <p style="margin: 0.5rem 0 0 0; font-size: 0.875rem;"><strong>Reason:</strong> {{ $appointment->reason }}</p>
                            <p style="margin: 0.25rem 0 0 0; font-size: 0.875rem;">{{ $appointment->appointment_date ? $appointment->appointment_date->format('M d, Y • h:i A') : 'TBD' }}</p>
                        </div>
                        <div style="text-align: right;">
                            <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                                <span style="display: inline-block; padding: 0.25rem 0.75rem; background: #10b981; color: white; border-radius: 0.25rem; font-size: 0.75rem; font-weight: 500;">PENDING</span>
                                <a href="{{ route('staff.appointments.confirm', $appointment) }}" class="btn btn-primary" style="padding: 0.5rem 0.75rem; font-size: 0.75rem; text-decoration: none; display: inline-block;">Review & Confirm</a>
                                <form method="POST" action="{{ route('staff.appointments.reject', $appointment) }}" style="margin: 0;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" onclick="return confirm('Are you sure you want to reject this appointment?')" style="padding: 0.5rem 0.75rem; background: #dc2626; color: white; border: none; border-radius: 0.375rem; font-size: 0.75rem; cursor: pointer; width: 100%;">Reject</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div style="padding: 1rem; margin-bottom: 1rem; background: #dcfce7; border-radius: 0.375rem; border-left: 4px solid #10b981;">
                    <p style="color: #065f46; margin: 0;">No routine appointments</p>
                </div>
            @endforelse
        </div>
    </div>

    <div class="card" style="margin-top: 2rem;">
        <h2>Queue Summary</h2>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 1rem;">
            <div style="padding: 1rem; background: #fee2e2; border-radius: 0.375rem; text-align: center;">
                <div style="font-size: 1.5rem; font-weight: bold; color: #ef4444;">{{ $emergencyQueue->count() }}</div>
                <p style="margin: 0.5rem 0 0 0; font-size: 0.875rem; color: #6b7280;">Emergency</p>
            </div>
            <div style="padding: 1rem; background: #fef3c7; border-radius: 0.375rem; text-align: center;">
                <div style="font-size: 1.5rem; font-weight: bold; color: #f59e0b;">{{ $highPriorityQueue->count() }}</div>
                <p style="margin: 0.5rem 0 0 0; font-size: 0.875rem; color: #6b7280;">High Priority</p>
            </div>
            <div style="padding: 1rem; background: #dcfce7; border-radius: 0.375rem; text-align: center;">
                <div style="font-size: 1.5rem; font-weight: bold; color: #10b981;">{{ $routineQueue->count() }}</div>
                <p style="margin: 0.5rem 0 0 0; font-size: 0.875rem; color: #6b7280;">Routine</p>
            </div>
            <div style="padding: 1rem; background: #dbeafe; border-radius: 0.375rem; text-align: center;">
                <div style="font-size: 1.5rem; font-weight: bold; color: #3b82f6;">{{ $pendingAppointments->count() }}</div>
                <p style="margin: 0.5rem 0 0 0; font-size: 0.875rem; color: #6b7280;">Total Pending</p>
            </div>
        </div>
    </div>

    <script>
        function filterQueue(type) {
            const sections = {
                'emergency': 'queue-emergency',
                'high': 'queue-high',
                'routine': 'queue-routine'
            };

            const buttons = {
                'all': 'filter-all',
                'emergency': 'filter-emergency',
                'high': 'filter-high',
                'routine': 'filter-routine'
            };

            // Show or hide sections
            if (type === 'all') {
                Object.values(sections).forEach(id => {
                    document.getElementById(id).style.display = 'block';
                });
            } else {
                Object.entries(sections).forEach(([key, id]) => {
                    document.getElementById(id).style.display = key === type ? 'block' : 'none';
                });
            }

            // Update button styles
            Object.entries(buttons).forEach(([key, id]) => {
                const button = document.getElementById(id);
                if (key === type || (type === 'all' && key === 'all')) {
                    button.style.textDecoration = 'underline';
                } else {
                    button.style.textDecoration = 'none';
                }
            });
        }
    </script>
@endsection
