@extends('layouts.app')

@section('sidebar')
    <a href="{{ route('pet-owner.dashboard') }}" class="sidebar-item"> Dashboard</a>
    <a href="{{ route('pet-owner.pets') }}" class="sidebar-item"> My Pets</a>
    <a href="{{ route('pet-owner.appointments') }}" class="sidebar-item active"> Appointments</a>
    

    <a href="{{ route('pet-owner.prescriptions') }}" class="sidebar-item"> Prescriptions</a>
    <a href="{{ route('pet-owner.notifications') }}" class="sidebar-item"> Notifications</a>
    <a href="#" class="sidebar-item" onclick="openPetCareAI(event)">Ask Pet Care AI</a>
@endsection

@section('content')
    <form action="{{ route('pet-owner.appointments.store') }}" method="POST" id="bookingForm">
        @csrf
        <div style="display: grid; grid-template-columns: 1fr; gap: 1.5rem; margin-bottom: 2rem;">
            <!-- Booking Form Card -->
            <div class="card">
                <h2 style="margin-top: 0;">Book an Appointment</h2>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem;">
                    <!-- Pet Selection -->
                    <div>
                        <label for="pet_id" style="display: block; margin-bottom: 0.5rem; font-weight: 500; font-size: 0.875rem;">Select Pet</label>
                        <select id="pet_id" name="pet_id" required style="width: 100%; padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 0.375rem; font-size: 0.875rem;">
                            <option value="">-- Choose a pet --</option>
                            @foreach($pets as $pet)
                                <option value="{{ $pet->id }}">{{ $pet->name }} ({{ $pet->species }})</option>
                            @endforeach
                        </select>
                        @error('pet_id')
                            <span style="color: #dc2626; font-size: 0.75rem; margin-top: 0.25rem; display: block;">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- DateTime Selection -->
                    <div>
                        <label for="appointment_date" style="display: block; margin-bottom: 0.5rem; font-weight: 500; font-size: 0.875rem;">Date & Time</label>
                        <input 
                            type="datetime-local" 
                            id="appointment_date" 
                            name="appointment_date" 
                            required
                            style="width: 100%; padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 0.375rem; font-size: 0.875rem;"
                        />
                        @error('appointment_date')
                            <span style="color: #dc2626; font-size: 0.75rem; margin-top: 0.25rem; display: block;">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Reason Selection -->
                    <div>
                        <label for="reason" style="display: block; margin-bottom: 0.5rem; font-weight: 500; font-size: 0.875rem;">Reason for Visit</label>
                        <select id="reason" name="reason" required style="width: 100%; padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 0.375rem; font-size: 0.875rem;">
                            <option value="">-- Select a reason --</option>
                            <option value="Regular Check-up">Regular Check-up</option>
                            <option value="Vaccination">Vaccination</option>
                            <option value="Follow-up Vaccination">Follow-up Vaccination</option>
                            <option value="Emergency">Emergency</option>
                        </select>
                        @error('reason')
                            <span style="color: #dc2626; font-size: 0.75rem; margin-top: 0.25rem; display: block;">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <!-- Summary & Submit -->
                <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid #e5e7eb;">
                    <div style="padding: 0.75rem; background: #dbeafe; border-radius: 0.375rem; margin-bottom: 1rem; font-size: 0.875rem;">
                        <strong>Information:</strong> <span id="selectedDateTime">Select pet, date, time and reason</span>
                    </div>
                    <button type="submit" class="btn btn-primary">Request Appointment</button>
                </div>
            </div>
        </div>
    </form>

    <script>
        function updateSelectedDateTime() {
            const datetimeInput = document.getElementById('appointment_date').value;
            const petSelect = document.getElementById('pet_id');
            const petName = petSelect.options[petSelect.selectedIndex].text;
            
            if (datetimeInput) {
                // Convert datetime-local format (YYYY-MM-DDTHH:mm) to readable format
                const datetime = new Date(datetimeInput);
                const formattedDate = datetime.toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
                const time = datetime.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: true });
                document.getElementById('selectedDateTime').textContent = `${petName} • ${formattedDate} at ${time}`;
            }
        }

        document.getElementById('appointment_date').addEventListener('change', updateSelectedDateTime);
        document.getElementById('pet_id').addEventListener('change', updateSelectedDateTime);
    </script>

    <div class="card" style="margin-top: 2rem;">
        <h2>Upcoming Appointments ({{ $upcomingAppointments->count() }})</h2>
        <div style="display: grid; gap: 1rem;">
            @forelse($upcomingAppointments as $appointment)
                @php
                    $borderColor = match($appointment->reason ?? 'Regular Check-up') {
                        'Emergency' => '#ef4444',
                        'Vaccination', 'Follow-up Vaccination' => '#f59e0b',
                        default => '#3b82f6',
                    };
                    $statusKey = strtolower($appointment->status);
                    $statusColor = match($statusKey) {
                        'pending' => '#f59e0b',
                        'approved' => '#10b981',
                        'completed' => '#10b981',
                        'missed' => '#ef4444',
                        'cancelled' => '#ef4444',
                        'rescheduled' => '#3b82f6',
                        default => '#3b82f6',
                    };
                    $statusDisplay = match($statusKey) {
                        'pending' => 'Pending Review',
                        'approved' => 'Confirmed',
                        'completed' => 'Completed',
                        'missed' => 'Missed',
                        'cancelled' => 'Cancelled',
                        'rescheduled' => 'Rescheduled',
                        default => 'Unknown',
                    };
                @endphp
                <div style="padding: 1rem; background: #ffffff; border-radius: 0.375rem; border-left: 4px solid {{ $borderColor }}; border: 1px solid #e5e7eb;">
                    <div style="display: flex; justify-content: space-between; align-items: start;">
                        <div style="flex: 1;">
                            <strong>{{ $appointment->reason ?? 'N/A' }} - {{ $appointment->pet->name }}</strong>
                            <p style="margin: 0.5rem 0 0 0; color: #6b7280; font-size: 0.875rem;">{{ $appointment->appointment_date->format('M d, Y') }} • {{ $appointment->appointment_date->format('h:i A') }}</p>
                            <p style="margin: 0.5rem 0 0 0; font-size: 0.875rem;">
                                @if($appointment->veterinarian)
                                    Dr. {{ $appointment->veterinarian->name }}
                                @else
                                    Awaiting veterinarian assignment
                                @endif
                            </p>
                        </div>
                        <div style="display: flex; flex-direction: column; align-items: flex-end; gap: 0.5rem;">
                            <span style="display: inline-block; padding: 0.25rem 0.75rem; background: {{ $statusColor }}; color: white; border-radius: 0.25rem; font-size: 0.75rem; font-weight: 500;">{{ $statusDisplay }}</span>
                            <div style="display: flex; gap: 0.5rem;">
                                <button type="button" class="btn-reschedule" data-id="{{ $appointment->id }}" style="padding: 0.5rem 0.75rem; background: #3b82f6; color: white; border: none; border-radius: 0.375rem; font-size: 0.75rem; cursor: pointer; transition: background 0.2s;">Reschedule</button>
                                <form method="POST" action="{{ route('pet-owner.appointments.cancel', $appointment->id) }}" style="margin: 0;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" onclick="return confirm('Are you sure you want to cancel this appointment?')" style="padding: 0.5rem 0.75rem; background: #ef4444; color: white; border: none; border-radius: 0.375rem; font-size: 0.75rem; cursor: pointer; transition: background 0.2s;">Cancel</button>
                                </form>
                            </div>
                            <!-- Reschedule Modal -->
                            <div id="reschedule-modal-{{ $appointment->id }}" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
                                <div style="background: white; padding: 2rem; border-radius: 0.5rem; width: 90%; max-width: 500px;">
                                    <h3 style="margin-top: 0;">Reschedule Appointment</h3>
                                    <p style="color: #6b7280; font-size: 0.875rem; margin-bottom: 1rem;">Current: {{ $appointment->appointment_date->format('M d, Y • h:i A') }}</p>
                                    <form method="POST" action="{{ route('pet-owner.appointments.reschedule', $appointment->id) }}">
                                        @csrf
                                        @method('PUT')
                                        <div style="margin-bottom: 1rem;">
                                            <label for="reschedule_datetime_{{ $appointment->id }}" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">New Date & Time</label>
                                            <input type="datetime-local" id="reschedule_datetime_{{ $appointment->id }}" name="appointment_date" value="{{ $appointment->appointment_date->format('Y-m-d\TH:i') }}" required style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 0.375rem;" />
                                        </div>
                                        <div style="display: flex; gap: 0.5rem; justify-content: flex-end;">
                                            <button type="button" onclick="closeRescheduleModal({{ $appointment->id }})" style="padding: 0.75rem 1rem; background: #d1d5db; border: none; border-radius: 0.375rem; cursor: pointer;">Cancel</button>
                                            <button type="submit" style="padding: 0.75rem 1rem; background: #667eea; color: white; border: none; border-radius: 0.375rem; cursor: pointer;">Reschedule</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <p style="color: #6b7280; text-align: center; padding: 2rem;">No upcoming appointments.</p>
            @endforelse
        </div>
    </div>

    <!-- Missed Appointments Section -->
    <div class="card" style="margin-top: 2rem;">
        <h2 style="color: #ef4444;">Missed Appointments ({{ $missedAppointments->count() }})</h2>
        <div style="display: grid; gap: 1rem;">
            @forelse($missedAppointments as $appointment)
                @php
                    $borderColor = match($appointment->reason ?? 'Regular Check-up') {
                        'Emergency' => '#ef4444',
                        'Vaccination', 'Follow-up Vaccination' => '#f59e0b',
                        default => '#3b82f6',
                    };
                    $statusKey = strtolower($appointment->status);
                    $statusColor = match($statusKey) {
                        'pending' => '#f59e0b',
                        'approved' => '#10b981',
                        'completed' => '#10b981',
                        'missed' => '#ef4444',
                        'cancelled' => '#ef4444',
                        'rescheduled' => '#3b82f6',
                        default => '#3b82f6',
                    };
                    $statusDisplay = match($statusKey) {
                        'pending' => 'Pending Review',
                        'approved' => 'Confirmed',
                        'completed' => 'Completed',
                        'missed' => 'Missed',
                        'cancelled' => 'Cancelled',
                        'rescheduled' => 'Rescheduled',
                        default => 'Unknown',
                    };
                @endphp
                <div style="padding: 1rem; background: #fef2f2; border-radius: 0.375rem; border-left: 4px solid #ef4444; border: 1px solid #fecaca; opacity: 0.85;">
                    <div style="display: flex; justify-content: space-between; align-items: start;">
                        <div style="flex: 1;">
                            <strong style="color: #991b1b;">{{ $appointment->reason ?? 'N/A' }} - {{ $appointment->pet->name }}</strong>
                            <p style="margin: 0.5rem 0 0 0; color: #6b7280; font-size: 0.875rem;">Was on {{ $appointment->appointment_date->format('M d, Y') }} at {{ $appointment->appointment_date->format('h:i A') }}</p>
                            <p style="margin: 0.5rem 0 0 0; font-size: 0.875rem; color: #dc2626;">
                                <strong>
                                    @if($statusKey === 'missed')
                                        Missed on user end
                                    @else
                                        This appointment was not completed
                                    @endif
                                </strong>
                            </p>
                            <p style="margin: 0.25rem 0 0 0; font-size: 0.875rem;">
                                @if($appointment->veterinarian)
                                    Dr. {{ $appointment->veterinarian->name }}
                                @else
                                    Awaiting veterinarian assignment
                                @endif
                            </p>
                        </div>
                        <div style="display: flex; flex-direction: column; align-items: flex-end; gap: 0.5rem;">
                            <span style="display: inline-block; padding: 0.25rem 0.75rem; background: #ef4444; color: white; border-radius: 0.25rem; font-size: 0.75rem; font-weight: 500;">{{ $statusDisplay }}</span>
                            <p style="margin: 0; font-size: 0.75rem; color: #6b7280;"><em>Cannot reschedule</em></p>
                        </div>
                    </div>
                </div>
            @empty
                <p style="color: #6b7280; text-align: center; padding: 2rem;">No missed appointments.</p>
            @endforelse
        </div>
    </div>

    <!-- Completed Appointments Section -->
    <div class="card" style="margin-top: 2rem;">
        <h2 style="color: #10b981;">Completed Appointments ({{ $completedAppointments->count() }})</h2>
        <div style="display: grid; gap: 1rem;">
            @forelse($completedAppointments as $appointment)
                @php
                    $borderColor = match($appointment->reason ?? 'Regular Check-up') {
                        'Emergency' => '#ef4444',
                        'Vaccination', 'Follow-up Vaccination' => '#f59e0b',
                        default => '#3b82f6',
                    };
                @endphp
                <div style="padding: 1rem; background: #f0fdf4; border-radius: 0.375rem; border-left: 4px solid #10b981; border: 1px solid #dbeafe;">
                    <div style="display: flex; justify-content: space-between; align-items: start;">
                        <div style="flex: 1;">
                            <strong>{{ $appointment->reason ?? 'N/A' }} - {{ $appointment->pet->name }}</strong>
                            <p style="margin: 0.5rem 0 0 0; color: #6b7280; font-size: 0.875rem;">Completed on {{ $appointment->appointment_date->format('M d, Y') }} at {{ $appointment->appointment_date->format('h:i A') }}</p>
                            <p style="margin: 0.5rem 0 0 0; font-size: 0.875rem;">
                                @if($appointment->veterinarian)
                                    Dr. {{ $appointment->veterinarian->name }}
                                @else
                                    Veterinarian not assigned
                                @endif
                            </p>
                        </div>
                        <div style="display: flex; flex-direction: column; align-items: flex-end; gap: 0.5rem;">
                            <span style="display: inline-block; padding: 0.25rem 0.75rem; background: #10b981; color: white; border-radius: 0.25rem; font-size: 0.75rem; font-weight: 500;">Completed</span>
                        </div>
                    </div>
                </div>
            @empty
                <p style="color: #6b7280; text-align: center; padding: 2rem;">No completed appointments.</p>
            @endforelse
        </div>
    </div>
    
    <script>
        function openRescheduleModal(appointmentId) {
            document.getElementById(`reschedule-modal-${appointmentId}`).style.display = 'flex';
        }

        function closeRescheduleModal(appointmentId) {
            document.getElementById(`reschedule-modal-${appointmentId}`).style.display = 'none';
        }

        // Setup reschedule buttons
        document.querySelectorAll('.btn-reschedule').forEach(btn => {
            btn.addEventListener('click', () => {
                const id = btn.dataset.id;
                openRescheduleModal(id);
            });
        });

        // Close modal on background click
        document.querySelectorAll('[id^="reschedule-modal-"]').forEach(modal => {
            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    const id = modal.id.split('-')[2];
                    closeRescheduleModal(id);
                }
            });
        });
    </script>
@endsection
