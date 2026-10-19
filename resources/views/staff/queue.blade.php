@extends('layouts.app')

@section('sidebar')
    @include('staff.sidebar')
@endsection

@section('content')
    <style>
        .queue-shell {
            display: grid;
            gap: 1.35rem;
        }

        .queue-panel {
            overflow: hidden;
            border: 1px solid rgba(245, 158, 11, 0.28);
            border-radius: 1.5rem;
            background: linear-gradient(180deg, rgba(255, 251, 235, 0.96), rgba(255, 255, 255, 0.94));
            box-shadow: 0 18px 36px rgba(23, 49, 61, 0.08);
        }

        .queue-panel-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            padding: 1.1rem 1.25rem 0.9rem;
            border-bottom: 1px solid rgba(23, 49, 61, 0.08);
        }

        .queue-panel-header h2 {
            margin: 0;
            font-size: 1.08rem;
            letter-spacing: -0.02em;
        }

        .queue-count {
            display: inline-flex;
            align-items: center;
            min-height: 30px;
            padding: 0.35rem 0.75rem;
            border-radius: 999px;
            background: rgba(245, 158, 11, 0.14);
            color: #b45309;
            font-size: 0.78rem;
            font-weight: 700;
            white-space: nowrap;
        }

        .queue-filter-controls {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
            padding: 1rem 1.25rem 0.75rem;
        }

        .queue-filter-btn {
            min-height: 38px;
            padding: 0.6rem 1rem;
            border: 0;
            border-radius: 0.75rem;
            color: #fff;
            cursor: pointer;
            font-size: 0.82rem;
            font-weight: 700;
            box-shadow: 0 10px 18px rgba(15, 23, 42, 0.12);
            transition: transform 0.18s ease, opacity 0.18s ease, box-shadow 0.18s ease;
        }

        .queue-filter-btn:hover {
            transform: translateY(-1px);
        }

        .queue-table-shell {
            max-height: 600px;
            overflow: auto;
            border: 1px solid rgba(23, 49, 61, 0.08);
            border-radius: 1rem;
            margin: 0 1.25rem 1.25rem;
            background: rgba(255, 255, 255, 0.92);
        }

        .queue-table {
            width: 100%;
            min-width: 900px;
            border-collapse: collapse;
        }

        .queue-table th,
        .queue-table td {
            padding: 1rem 1.1rem;
            text-align: left;
            border-bottom: 1px solid rgba(23, 49, 61, 0.08);
            vertical-align: middle;
        }

        .queue-table thead th {
            position: sticky;
            top: 0;
            z-index: 2;
            background: #f8fafc;
            color: #475569;
            font-size: 0.75rem;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .queue-table tbody tr {
            background: rgba(255, 251, 235, 0.7);
            transition: background-color 0.16s ease;
        }

        .queue-table tbody tr:hover {
            background: rgba(245, 158, 11, 0.1);
        }

        .queue-table tbody tr[data-type="emergency"] {
            border-left: 4px solid #dc2626;
            background: rgba(254, 242, 242, 0.9);
        }

        .queue-table tbody tr[data-type="emergency"]:hover {
            background: #fee2e2;
        }

        .queue-status {
            display: inline-flex;
            align-items: center;
            padding: 0.38rem 0.7rem;
            border-radius: 999px;
            background: rgba(245, 158, 11, 0.15);
            color: #92400e;
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 0.03em;
            white-space: nowrap;
        }

        .queue-status--urgent {
            background: rgba(220, 38, 38, 0.12);
            color: #991b1b;
        }

        .queue-actions {
            display: flex;
            justify-content: center;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .action-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 36px;
            padding: 0.6rem 0.9rem;
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: #fff;
            text-decoration: none;
            font-weight: 700;
            font-size: 0.78rem;
            box-shadow: 0 12px 20px rgba(217, 119, 6, 0.22);
            white-space: nowrap;
        }

        .queue-reject-btn {
            min-height: 36px;
            padding: 0.55rem 0.8rem;
            border: 0;
            border-radius: 0.8rem;
            background: linear-gradient(135deg, #dc6257, #b91c1c);
            color: #fff;
            cursor: pointer;
            font-size: 0.78rem;
            font-weight: 700;
            box-shadow: 0 10px 18px rgba(185, 28, 28, 0.18);
        }

        @media (max-width: 768px) {
            .queue-panel-header {
                align-items: flex-start;
                flex-direction: column;
            }

            .queue-filter-controls {
                padding-top: 0.75rem;
            }

            .queue-filter-btn {
                flex: 1 1 calc(50% - 0.5rem);
                min-width: 0;
            }

            .queue-table-shell {
                margin: 0 0.75rem 0.75rem;
                max-height: 65vh;
                -webkit-overflow-scrolling: touch;
            }

            .queue-table {
                min-width: 900px;
            }
        }
    </style>

    <div class="queue-shell">
        <section class="hero-card">
            <div class="hero-row">
                <div>
                    <h1 class="hero-title">Appointment Queue</h1>
                    <p class="hero-copy">Review pending appointment requests, prioritizing urgent cases before confirming or rejecting.</p>
                </div>
            </div>
        </section>

        <section class="queue-panel">
            <div class="queue-panel-header">
                <div>
                    <h2>Pending Appointments</h2>
                    <p class="section-copy">Review requests by visit type and take action.</p>
                </div>
                <span class="queue-count">{{ $pendingAppointments->count() }} pending</span>
            </div>

            <div class="queue-filter-controls" aria-label="Filter appointments by reason">
                <button type="button" onclick="filterQueueTable('all')" class="queue-filter-btn" data-filter="all" style="background: #d97706;">All</button>
                <button type="button" onclick="filterQueueTable('regular')" class="queue-filter-btn" data-filter="regular" style="background: #10b981;">Regular Check-up</button>
                <button type="button" onclick="filterQueueTable('vaccination')" class="queue-filter-btn" data-filter="vaccination" style="background: #3b82f6;">Vaccination</button>
                <button type="button" onclick="filterQueueTable('emergency')" class="queue-filter-btn" data-filter="emergency" style="background: #dc2626;">Emergency</button>
                <button type="button" onclick="filterQueueTable('other')" class="queue-filter-btn" data-filter="other" style="background: #64748b;">Other</button>
            </div>

            <div class="queue-table-shell">
                <table class="queue-table">
                    <thead>
                        <tr>
                            <th>Pet Name</th>
                            <th>Species</th>
                            <th>Owner</th>
                            <th>Phone</th>
                            <th>Reason</th>
                            <th>Date & Time</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="queueTableBody">
                    @php
                        $allAppointments = collect();
                        if ($emergencyQueue->count() > 0) {
                            $allAppointments = $allAppointments->concat($emergencyQueue);
                        }
                        if ($highPriorityQueue->count() > 0) {
                            $allAppointments = $allAppointments->concat($highPriorityQueue);
                        }
                        if ($routineQueue->count() > 0) {
                            $allAppointments = $allAppointments->concat($routineQueue);
                        }
                    @endphp
                    
                    @forelse($allAppointments as $appointment)
                        @php
                            $reason = $appointment->reason_for_visit ?? $appointment->reason ?? 'Regular Check-up';
                            $reasonLower = strtolower($reason);
                            $appointmentType = match (true) {
                                str_contains($reasonLower, 'emergency') => 'emergency',
                                str_contains($reasonLower, 'vaccination') => 'vaccination',
                                str_contains($reasonLower, 'regular check-up') => 'regular',
                                default => 'other',
                            };
                        @endphp
                        <tr class="queue-row" data-type="{{ $appointmentType }}">
                            <td><strong>{{ $appointment->pet->name }}</strong></td>
                            <td>{{ $appointment->pet->species }}</td>
                            <td>{{ $appointment->pet->owner->name }}</td>
                            <td>{{ $appointment->pet->owner->phone ?? 'N/A' }}</td>
                            <td>{{ $reason }}</td>
                            <td class="queue-date">{{ $appointment->appointment_date ? $appointment->appointment_date->format('M d, Y • h:i A') : 'TBD' }}</td>
                            <td>
                                <span class="queue-status {{ $appointmentType === 'emergency' ? 'queue-status--urgent' : '' }}">{{ $appointmentType === 'emergency' ? 'URGENT' : 'PENDING' }}</span>
                            </td>
                            <td>
                                <div class="queue-actions">
                                    <a href="{{ route('staff.appointments.confirm', $appointment->appointment_id) }}" class="action-btn">Confirm</a>
                                    <form method="POST" action="{{ route('staff.appointments.reject', $appointment->appointment_id) }}" style="margin: 0;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="queue-reject-btn" onclick="return confirm('Reject this appointment?')">Reject</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" style="padding: 1.5rem; text-align: center; color: #6b7280;">No pending appointments</td>
                        </tr>
                    @endforelse
                </tbody>
                </table>
            </div>
        </section>
    </div>

  

    <script>
    function filterQueueTable(filterType) {
        const rows = document.querySelectorAll('.queue-row');
        
        rows.forEach(row => {
            const type = row.getAttribute('data-type');
            const matches = filterType === 'all' || type === filterType;
            row.style.display = matches ? '' : 'none';
        });
        
        // Update button styles
        document.querySelectorAll('.queue-filter-btn').forEach(btn => {
            if (btn.getAttribute('data-filter') === filterType) {
                btn.style.opacity = '1';
                btn.style.boxShadow = '0 0 0 2px rgba(0,0,0,0.2)';
            } else {
                btn.style.opacity = '0.6';
                btn.style.boxShadow = 'none';
            }
        });
    }
    
    // Initialize with emergency appointments on top
    document.addEventListener('DOMContentLoaded', function() {
        const tbody = document.getElementById('queueTableBody');
        const rows = Array.from(document.querySelectorAll('.queue-row'));
        
        rows.sort((a, b) => {
            const typeA = a.getAttribute('data-type');
            const typeB = b.getAttribute('data-type');
            if (typeA === 'emergency' && typeB !== 'emergency') return -1;
            if (typeA !== 'emergency' && typeB === 'emergency') return 1;
            return 0;
        });
        
        rows.forEach(row => tbody.appendChild(row));
        
        // Highlight all button initially
        document.querySelectorAll('.queue-filter-btn').forEach(btn => {
            btn.style.opacity = btn.getAttribute('data-filter') === 'all' ? '1' : '0.6';
        });
    });
    </script>
@endsection
