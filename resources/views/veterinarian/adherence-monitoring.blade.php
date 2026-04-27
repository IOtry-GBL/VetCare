@extends('layouts.app')

@section('title', 'Medication Adherence Monitoring')

@section('sidebar')
    <a href="{{ route('vet.dashboard') }}" class="sidebar-item">Dashboard</a>
    <a href="{{ route('vet.appointments') }}" class="sidebar-item">Appointments</a>

    <a href="{{ route('vet.medical-records') }}" class="sidebar-item">Medical Records</a>
    <a href="{{ route('vet.prescriptions') }}" class="sidebar-item">E-Prescriptions</a>
    <a href="{{ route('vet.adherence-monitoring') }}" class="sidebar-item active">Medication Adherence</a>
@endsection

@section('content')
    <section class="hero-card">
        <div class="hero-row">
            <div>
                <h1 class="hero-title">Medication Adherence Monitoring</h1>
                <p class="hero-copy">
                    Track medication compliance across your patients, identify non-adherent cases, and intervene early to improve treatment outcomes.
                </p>
            </div>

            <div class="action-row">
                <a href="{{ route('vet.prescriptions') }}" class="btn btn-secondary">Create Prescription</a>
            </div>
        </div>
    </section>

    <!-- Adherence Metrics -->
    <section class="stat-grid">
        <div class="widget">
            <span class="widget-title">Overall Adherence Rate</span>
            <strong class="widget-value">{{ $adherenceRate }}%</strong>
            <p class="muted-copy">
                @if ($adherenceRate >= 80)
                    <span style="color: #10b981;">Excellent adherence</span>
                @elseif ($adherenceRate >= 60)
                    <span style="color: #f59e0b;">Moderate adherence</span>
                @else
                    <span style="color: #ef4444;">Needs improvement</span>
                @endif
            </p>
        </div>

        <div class="widget">
            <span class="widget-title">Active Prescriptions</span>
            <strong class="widget-value">{{ $totalPrescriptions }}</strong>
            <p class="muted-copy">Currently tracked medications.</p>
        </div>

        <div class="widget">
            <span class="widget-title">Low Adherence Cases</span>
            <strong class="widget-value">{{ $lowAdherencePrescriptions->count() }}</strong>
            <p class="muted-copy">Prescriptions with < 70% taken doses.</p>
        </div>

        <div class="widget">
            <span class="widget-title">Tracked Doses</span>
            <strong class="widget-value">{{ $prescriptionsWithLogs }}</strong>
            <p class="muted-copy">Medications with adherence logs.</p>
        </div>
    </section>

    <!-- Adherence Status Toggle -->
    <section class="card">
        <div class="surface-header">
            <div>
                <h2>Prescription Status Filter</h2>
                <p class="section-copy">View prescriptions by adherence status.</p>
            </div>
        </div>

        <div style="display: flex; gap: 1rem; padding: 1.5rem 0; border-bottom: 1px solid #e5e7eb;">
            <button 
                class="btn btn-secondary"
                onclick="document.querySelector('[data-filter=all]').style.display = 'block'; document.querySelector('[data-filter=low]').style.display = 'none'; this.style.fontWeight = 'bold'; document.querySelectorAll('.filter-btn').forEach(b => b.style.fontWeight = 'normal'); this.style.fontWeight = 'bold';"
                class="filter-btn"
                style="cursor: pointer; padding: 0.5rem 1rem; border-radius: 0.375rem;">
                All Prescriptions ({{ $totalPrescriptions }})
            </button>
            <button 
                class="btn btn-secondary filter-btn"
                onclick="document.querySelector('[data-filter=all]').style.display = 'none'; document.querySelector('[data-filter=low]').style.display = 'block'; this.style.fontWeight = 'bold'; document.querySelectorAll('.filter-btn').forEach(b => b.style.fontWeight = 'normal'); this.style.fontWeight = 'bold';"
                style="cursor: pointer; padding: 0.5rem 1rem; border-radius: 0.375rem;">
                Low Adherence ({{ $lowAdherencePrescriptions->count() }})
            </button>
        </div>
    </section>

    <!-- All Prescriptions List -->
    <section class="card" data-filter="all">
        <div class="surface-header">
            <div>
                <h2>All Prescriptions</h2>
                <p class="section-copy">View detailed adherence metrics for each prescription.</p>
            </div>
        </div>

        <div class="list-grid" style="display: grid; gap: 1rem;">
            @forelse ($prescriptions as $prescription)
                @php
                    $adherenceLogs = is_iterable($prescription->adherenceLogs) ? $prescription->adherenceLogs : collect();
                    $confirmedDoses = $adherenceLogs->where('intake_status', 'Taken')->count();
                    $totalScheduled = $adherenceLogs->count();
                    $prescriptionAdherence = $totalScheduled > 0 
                        ? round(($confirmedDoses / $totalScheduled) * 100, 2)
                        : 0;
                    $statusColor = $prescriptionAdherence >= 80 ? '#10b981' : ($prescriptionAdherence >= 60 ? '#f59e0b' : '#ef4444');
                @endphp

                <article class="list-card" style="border-left: 4px solid {{ $statusColor }};">
                    <div>
                        <div class="item-title" style="display: flex; justify-content: space-between; align-items: center;">
                            <span>{{ $prescription->medication_name }}</span>
                            <span style="font-size: 0.875rem; background-color: {{ $statusColor }}; color: white; padding: 0.25rem 0.75rem; border-radius: 9999px; font-weight: 600;">
                                {{ $prescriptionAdherence }}%
                            </span>
                        </div>

                        <div class="item-meta" style="margin-top: 0.5rem;">
                            <span class="pill pill-neutral">
                                Dosage: {{ $prescription->dosage }}
                            </span>
                            <span class="pill pill-neutral">
                                Frequency: {{ $prescription->frequency }}
                            </span>
                            <span class="pill pill-neutral">
                                Duration: {{ $prescription->duration }}
                            </span>
                        </div>

                        <div style="margin-top: 0.75rem;">
                            <p class="item-copy" style="margin: 0;">
                                <strong>Patient:</strong> {{ $prescription->medicalRecord->pet->name ?? 'Unknown' }} ({{ $prescription->medicalRecord->pet->species ?? 'N/A' }})
                            </p>
                            <p class="item-copy" style="margin-top: 0.35rem;">
                                <strong>Owner:</strong> {{ $prescription->medicalRecord->pet->owner->name ?? 'Unknown Owner' }}
                            </p>
                            <p class="item-copy" style="margin-top: 0.35rem;">
                                <strong>Issued:</strong> {{ $prescription->issued_at?->timezone(\App\Services\AdherenceService::clinicTimezone())->format('M d, Y g:i A') ?? 'N/A' }} PH
                            </p>
                        </div>

                        <!-- Adherence Details -->
                        @if ($totalScheduled > 0)
                            <div style="margin-top: 1rem; padding: 0.75rem; background-color: #f9fafb; border-radius: 0.375rem;">
                                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 0.5rem; font-size: 0.875rem;">
                                    <div>
                                        <span style="color: #6b7280; display: block; margin-bottom: 0.25rem;">Taken</span>
                                        <strong style="color: #10b981;">{{ $confirmedDoses }}</strong>
                                    </div>
                                    <div>
                                        <span style="color: #6b7280; display: block; margin-bottom: 0.25rem;">Missed</span>
                                        <strong style="color: #ef4444;">{{ $adherenceLogs->where('intake_status', 'Missed')->count() }}</strong>
                                    </div>
                                    <div>
                                        <span style="color: #6b7280; display: block; margin-bottom: 0.25rem;">Pending</span>
                                        <strong style="color: #6366f1;">{{ $adherenceLogs->where('intake_status', 'Pending')->count() }}</strong>
                                    </div>
                                </div>

                                <!-- Progress Bar -->
                                <div style="margin-top: 0.75rem; background-color: #e5e7eb; height: 0.5rem; border-radius: 9999px; overflow: hidden;">
                                    <div style="height: 100%; background-color: {{ $statusColor }}; width: {{ $prescriptionAdherence }}%; transition: width 0.3s ease;"></div>
                                </div>
                            </div>
                        @else
                            <div style="margin-top: 1rem; padding: 0.75rem; background-color: #fef3c7; border-radius: 0.375rem; color: #92400e; font-size: 0.875rem;">
                                No adherence logs recorded yet.
                            </div>
                        @endif
                    </div>

                    <div class="action-row" style="justify-content: flex-end; gap: 0.5rem;">
                        <button 
                            onclick="toggleDetails('prescription-{{ $prescription->prescription_id }}')"
                            class="btn btn-secondary"
                            style="font-size: 0.875rem; padding: 0.5rem 1rem;">
                            View Details
                        </button>
                    </div>
                </article>

                <!-- Prescription Details (Hidden by default) -->
                <div 
                    id="prescription-{{ $prescription->prescription_id }}" 
                    style="display: none; grid-column: 1 / -1; margin-top: -1rem; margin-bottom: 0.5rem; padding: 1.5rem; background-color: #f9fafb; border: 1px solid #e5e7eb; border-radius: 0.375rem;">
                    
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                        <h3 style="margin: 0; font-size: 1rem; font-weight: 600;">Adherence Log History</h3>
                        <button onclick="toggleDetails('prescription-{{ $prescription->prescription_id }}')" style="background: none; border: none; font-size: 1.5rem; cursor: pointer; color: #6b7280;">&times;</button>
                    </div>

                    @if ($adherenceLogs->isNotEmpty())
                        <div style="overflow-x: auto;">
                            <table style="width: 100%; font-size: 0.875rem; border-collapse: collapse;">
                                <thead>
                                    <tr style="border-bottom: 2px solid #e5e7eb;">
                                        <th style="text-align: left; padding: 0.75rem; font-weight: 600; color: #374151;">Scheduled Date</th>
                                        <th style="text-align: left; padding: 0.75rem; font-weight: 600; color: #374151;">Status</th>
                                        <th style="text-align: left; padding: 0.75rem; font-weight: 600; color: #374151;">Confirmed At</th>
                                        <th style="text-align: left; padding: 0.75rem; font-weight: 600; color: #374151;">Remarks</th>
                                        <th style="text-align: center; padding: 0.75rem; font-weight: 600; color: #374151;">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($adherenceLogs->sortByDesc('scheduled_datetime') as $log)
                                        <tr style="border-bottom: 1px solid #e5e7eb; hover: background-color: #f3f4f6;">
                                            <td style="padding: 0.75rem;">
                                                {{ $log->scheduled_datetime?->timezone(\App\Services\AdherenceService::clinicTimezone())->format('M d, Y g:i A') ?? 'N/A' }} PH
                                            </td>
                                            <td style="padding: 0.75rem;">
                                                <span style="
                                                    padding: 0.25rem 0.75rem;
                                                    border-radius: 0.375rem;
                                                    font-weight: 500;
                                                    display: inline-block;
                                                    @switch($log->intake_status)
                                                        @case('Taken')
                                                            background-color: #d1fae5;
                                                            color: #065f46;
                                                            @break
                                                        @case('Missed')
                                                            background-color: #fee2e2;
                                                            color: #7f1d1d;
                                                            @break
                                                        @case('Delayed')
                                                            background-color: #e0e7ff;
                                                            color: #3730a3;
                                                            @break
                                                        @default
                                                            background-color: #f3f4f6;
                                                            color: #6b7280;
                                                    @endswitch
                                                ">
                                                    {{ $log->intake_status ?? 'Pending' }}
                                                </span>
                                            </td>
                                            <td style="padding: 0.75rem;">
                                            {{ $log->confirmation_time?->timezone(\App\Services\AdherenceService::clinicTimezone())->format('M d, Y g:i A') ?? '—' }}
                                            </td>
                                            <td style="padding: 0.75rem; max-width: 200px; word-break: break-word;">
                                                {{ $log->remarks ?? '—' }}
                                            </td>
                                            <td style="padding: 0.75rem; text-align: center;">
                                                <button 
                                                    onclick="openUpdateModal('{{ $prescription->prescription_id }}', '{{ $log->adherence_id }}')"
                                                    class="btn btn-secondary"
                                                    style="font-size: 0.75rem; padding: 0.25rem 0.5rem;">
                                                    Edit
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Add New Log Form -->
                        <div style="margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid #e5e7eb;">
                            <h4 style="font-weight: 600; margin-bottom: 1rem;">Log New Dose</h4>
                            <form action="{{ route('vet.adherence.update', $prescription->prescription_id) }}" method="POST" style="display: grid; gap: 1rem;">
                                @csrf
                                <div>
                                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: #374151;">Scheduled Date & Time</label>
                                    <input 
                                        type="datetime-local" 
                                        name="scheduled_datetime" 
                                        required
                                        style="width: 100%; padding: 0.5rem 0.75rem; border: 1px solid #d1d5db; border-radius: 0.375rem; font-size: 0.875rem;">
                                </div>

                                <div>
                                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: #374151;">Intake Status</label>
                                    <select name="intake_status" required style="width: 100%; padding: 0.5rem 0.75rem; border: 1px solid #d1d5db; border-radius: 0.375rem; font-size: 0.875rem;">
                                        <option value="">Select Status</option>
                                        <option value="Taken">Taken - Dose was given</option>
                                        <option value="Missed">Missed - Dose was not given</option>
                                        <option value="Delayed">Delayed - Dose given late</option>
                                    </select>
                                </div>

                                <div>
                                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: #374151;">Remarks (Optional)</label>
                                    <textarea 
                                        name="remarks" 
                                        placeholder="Clinical notes or reasons for status..."
                                        style="width: 100%; padding: 0.5rem 0.75rem; border: 1px solid #d1d5db; border-radius: 0.375rem; font-size: 0.875rem; resize: vertical; min-height: 80px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;"></textarea>
                                </div>

                                <div style="display: flex; gap: 1rem;">
                                    <button type="submit" class="btn btn-primary">Log Adherence</button>
                                    <button type="button" onclick="toggleDetails('prescription-{{ $prescription->prescription_id }}')" class="btn btn-secondary">Cancel</button>
                                </div>
                            </form>
                        </div>
                    @else
                        <div style="padding: 1.5rem; text-align: center; color: #6b7280;">
                            <p>No adherence logs recorded yet. Click "Log Adherence" to start tracking this prescription.</p>
                        </div>
                    @endif
                </div>
            @empty
                <div class="empty-state">
                    <p style="font-size: 1rem; margin: 0;">No prescriptions found for your patients.</p>
                    <p style="font-size: 0.875rem; color: #6b7280; margin-top: 0.5rem; margin-bottom: 1.5rem;">Start by creating a prescription to track medication adherence.</p>
                    <a href="{{ route('vet.prescriptions') }}" class="btn btn-primary">Create Prescription</a>
                </div>
            @endforelse
        </div>
    </section>

    <!-- Low Adherence Cases -->
    <section class="card" data-filter="low" style="display: none;">
        <div class="surface-header">
            <div>
                <h2>At-Risk Cases (< 70% Adherence)</h2>
                <p class="section-copy">Prescriptions requiring intervention or follow-up with pet owners.</p>
            </div>
        </div>

        <div class="list-grid" style="display: grid; gap: 1rem;">
            @forelse ($lowAdherencePrescriptions as $prescription)
                @php
                    $adherenceLogs = is_iterable($prescription->adherenceLogs) ? $prescription->adherenceLogs : collect();
                    $confirmedDoses = $adherenceLogs->where('intake_status', 'Taken')->count();
                    $totalScheduled = $adherenceLogs->count();
                    $prescriptionAdherence = $totalScheduled > 0 
                        ? round(($confirmedDoses / $totalScheduled) * 100, 2)
                        : 0;
                @endphp

                <article class="list-card" style="border-left: 4px solid #ef4444;">
                    <div>
                        <div class="item-title" style="display: flex; justify-content: space-between; align-items: center;">
                            <span>{{ $prescription->medication_name }}</span>
                            <span style="font-size: 0.875rem; background-color: #ef4444; color: white; padding: 0.25rem 0.75rem; border-radius: 9999px; font-weight: 600;">
                                {{ $prescriptionAdherence }}%
                            </span>
                        </div>

                        <div style="margin-top: 0.75rem;">
                            <p class="item-copy" style="margin: 0;">
                                <strong>Patient:</strong> {{ $prescription->medicalRecord->pet->name ?? 'Unknown' }} ({{ $prescription->medicalRecord->pet->species ?? 'N/A' }})
                            </p>
                            <p class="item-copy" style="margin-top: 0.35rem;">
                                <strong>Owner:</strong> {{ $prescription->medicalRecord->pet->owner->name ?? 'Unknown Owner' }}
                            </p>
                            <p class="item-copy" style="margin-top: 0.35rem;">
                                <strong>Contact:</strong> {{ $prescription->medicalRecord->pet->owner->email ?? 'N/A' }}
                            </p>
                        </div>

                        @if ($totalScheduled > 0)
                            <div style="margin-top: 1rem; padding: 0.75rem; background-color: #fee2e2; border-radius: 0.375rem; border-left: 3px solid #ef4444;">
                                <p style="margin: 0; font-size: 0.875rem; color: #7f1d1d; font-weight: 500;">
                                    Warning: Only {{ $confirmedDoses }} of {{ $totalScheduled }} doses marked as taken. Recommend follow-up contact.
                                </p>
                            </div>
                        @endif
                    </div>

                    <div class="action-row" style="justify-content: flex-end; gap: 0.5rem;">
                        <button 
                            onclick="toggleDetails('low-prescription-{{ $prescription->prescription_id }}')"
                            class="btn btn-secondary"
                            style="font-size: 0.875rem; padding: 0.5rem 1rem;">
                            View & Update
                        </button>
                    </div>
                </article>

                <!-- Low Adherence Details -->
                <div 
                    id="low-prescription-{{ $prescription->prescription_id }}" 
                    style="display: none; grid-column: 1 / -1; margin-top: -1rem; margin-bottom: 0.5rem; padding: 1.5rem; background-color: #fef2f2; border: 2px solid #ef4444; border-radius: 0.375rem;">
                    
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                        <h3 style="margin: 0; font-size: 1rem; font-weight: 600;">Intervention Checklist</h3>
                        <button onclick="toggleDetails('low-prescription-{{ $prescription->prescription_id }}')" style="background: none; border: none; font-size: 1.5rem; cursor: pointer; color: #6b7280;">&times;</button>
                    </div>

                    <div style="background: white; padding: 1rem; border-radius: 0.375rem; margin-bottom: 1rem;">
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; font-size: 0.875rem;">
                            <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                                <input type="checkbox" style="cursor: pointer;">
                                <span>Call owner to discuss barriers</span>
                            </label>
                            <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                                <input type="checkbox" style="cursor: pointer;">
                                <span>Simplify dosage schedule</span>
                            </label>
                            <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                                <input type="checkbox" style="cursor: pointer;">
                                <span>Consider alternative medication</span>
                            </label>
                            <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                                <input type="checkbox" style="cursor: pointer;">
                                <span>Provide written instructions</span>
                            </label>
                            <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                                <input type="checkbox" style="cursor: pointer;">
                                <span>Schedule follow-up appointment</span>
                            </label>
                            <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                                <input type="checkbox" style="cursor: pointer;">
                                <span>Document intervention in notes</span>
                            </label>
                        </div>
                    </div>

                    <form action="{{ route('vet.adherence.update', $prescription->prescription_id) }}" method="POST">
                        @csrf
                        <div>
                            <label style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: #374151;">Add Clinical Note</label>
                            <textarea 
                                name="remarks" 
                                placeholder="Document your intervention and observations..."
                                style="width: 100%; padding: 0.5rem 0.75rem; border: 1px solid #ef4444; border-radius: 0.375rem; font-size: 0.875rem; resize: vertical; min-height: 100px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;"></textarea>
                        </div>

                        <div style="display: flex; gap: 1rem; margin-top: 1rem;">
                            <button type="submit" class="btn btn-primary">Save Intervention</button>
                            <button type="button" onclick="toggleDetails('low-prescription-{{ $prescription->prescription_id }}')" class="btn btn-secondary">Done</button>
                        </div>
                    </form>
                </div>
            @empty
                <div class="empty-state">
                    <p style="font-size: 1rem; margin: 0;">Great news! No low-adherence cases detected.</p>
                    <p style="font-size: 0.875rem; color: #6b7280; margin-top: 0.5rem;">Your patients are maintaining good medication compliance.</p>
                </div>
            @endforelse
        </div>
    </section>

    <script>
        function toggleDetails(id) {
            const element = document.getElementById(id);
            if (element) {
                element.style.display = element.style.display === 'none' ? 'block' : 'none';
            }
        }

        function openUpdateModal(prescriptionId, adherenceId) {
            // Placeholder for modal opening
            console.log(`Update adherence log ${adherenceId} for prescription ${prescriptionId}`);
            alert('Edit functionality can be expanded to show a modal form.');
        }
    </script>
@endsection
