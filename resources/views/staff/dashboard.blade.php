@extends('layouts.app')

@section('sidebar')
    @include('staff.sidebar')
@endsection

@section('content')
    <style>
        .dashboard-shell {
            display: grid;
            gap: 1.35rem;
        }

        .dashboard-metrics {
            gap: 1rem;
        }

        .dashboard-metrics .widget {
            border: 1px solid rgba(23, 49, 61, 0.08);
            background: linear-gradient(180deg, rgba(255,255,255,0.92), rgba(248,250,252,0.88));
            box-shadow: 0 18px 36px rgba(23, 49, 61, 0.08);
        }

        .dashboard-panel {
            border: 1px solid rgba(23, 49, 61, 0.08);
            border-radius: 1.5rem;
            background: rgba(255, 255, 255, 0.9);
            box-shadow: 0 18px 36px rgba(23, 49, 61, 0.08);
            overflow: hidden;
        }

        .dashboard-panel--warning {
            border-color: rgba(245, 158, 11, 0.28);
            background: linear-gradient(180deg, rgba(255,251,235,0.96), rgba(255,255,255,0.92));
        }

        .dashboard-panel--info {
            border-color: rgba(59, 130, 246, 0.25);
            background: linear-gradient(180deg, rgba(248,251,255,0.96), rgba(255,255,255,0.92));
        }

        .panel-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            padding: 1.1rem 1.25rem 0.9rem;
            border-bottom: 1px solid rgba(23, 49, 61, 0.08);
            background: rgba(255,255,255,0.2);
        }

        .panel-header h3 {
            margin: 0;
            font-size: 1.08rem;
            letter-spacing: -0.02em;
        }

        .count-pill {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 30px;
            padding: 0.35rem 0.75rem;
            border-radius: 999px;
            font-size: 0.78rem;
            font-weight: 700;
            letter-spacing: 0.02em;
        }

        .count-pill.warning {
            background: rgba(245, 158, 11, 0.14);
            color: #b45309;
        }

        .count-pill.info {
            background: rgba(59, 130, 246, 0.12);
            color: #1d4ed8;
        }

        .panel-controls {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
            padding: 1rem 1.25rem 0.75rem;
            background: rgba(255,255,255,0.2);
        }

        .filter-btn {
            padding: 0.6rem 1rem;
            border: none;
            border-radius: 0.75rem;
            cursor: pointer;
            font-weight: 700;
            font-size: 0.82rem;
            color: #fff;
            box-shadow: 0 10px 18px rgba(15, 23, 42, 0.12);
            transition: transform 0.18s ease, opacity 0.18s ease;
        }

        .filter-btn:hover {
            transform: translateY(-1px);
        }

        .table-shell {
            overflow-x: auto;
            overflow-y: auto;
            max-height: 460px;
        }

        .dashboard-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 760px;
        }

        .dashboard-table th,
        .dashboard-table td {
            padding: 1rem 1.25rem;
            text-align: left;
            border-bottom: 1px solid rgba(23, 49, 61, 0.08);
            vertical-align: middle;
        }

        .dashboard-table thead th {
            background: rgba(148, 163, 184, 0.06);
            color: #475569;
            font-size: 0.78rem;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            font-weight: 800;
        }

        .dashboard-table tbody tr:hover {
            background: rgba(59, 130, 246, 0.025);
        }

        .action-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 36px;
            padding: 0.6rem 0.9rem;
            border-radius: 0.8rem;
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: #fff;
            text-decoration: none;
            font-weight: 700;
            font-size: 0.78rem;
            box-shadow: 0 12px 20px rgba(217, 119, 6, 0.22);
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.38rem 0.7rem;
            border-radius: 999px;
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 0.03em;
        }

        .status-badge.warning {
            background: rgba(245, 158, 11, 0.15);
            color: #92400e;
        }

        @media (max-width: 768px) {
            .panel-header {
                align-items: flex-start;
                flex-direction: column;
            }

            .panel-controls {
                padding-top: 0.75rem;
            }

            .filter-btn {
                flex: 1 1 calc(50% - 0.5rem);
                min-width: 0;
            }
        }
    </style>

    <div class="dashboard-shell">
        <div class="grid dashboard-metrics">
            <div class="widget">
                <div class="widget-title">Today's Approved Appointments</div>
                <div class="widget-value">{{ $todayAppointments }}</div>
                <p style="color: #6b7280; margin-top: 0.5rem;">Approved and scheduled for today</p>
            </div>

            <div class="widget">
                <div class="widget-title">Pending Confirmations</div>
                <div class="widget-value" style="color: var(--color-visit);">{{ $pending }}</div>
                <p style="color: #6b7280; margin-top: 0.5rem;">Awaiting action</p>
            </div>

            <div class="widget">
                <div class="widget-title">Total Appointments</div>
                <div class="widget-value">{{ $allAppointments }}</div>
                <p style="color: #6b7280; margin-top: 0.5rem;">In the system</p>
            </div>
        </div>

        <div class="dashboard-panel dashboard-panel--warning">
            <div class="panel-header">
                <h3>Pending Confirmations</h3>
                <span class="count-pill warning">{{ $pendingRequests->count() }} pending</span>
            </div>

            @if($pendingRequests->count() > 0)
                <div class="panel-controls">
                    <button onclick="filterPendingAppointments('all')" class="filter-btn" data-filter="all" style="background: #d97706;">All</button>
                    <button onclick="filterPendingAppointments('regular')" class="filter-btn" data-filter="regular" style="background: #10b981;">Regular Check-up</button>
                    <button onclick="filterPendingAppointments('vaccination')" class="filter-btn" data-filter="vaccination" style="background: #3b82f6;">Vaccination</button>
                    <button onclick="filterPendingAppointments('emergency')" class="filter-btn" data-filter="emergency" style="background: #dc2626;">Emergency</button>
                </div>

                <div class="table-shell">
                    <table class="dashboard-table">
                        <thead>
                            <tr>
                                <th>Pet Name</th>
                                <th>Species</th>
                                <th>Owner</th>
                                <th>Phone</th>
                                <th>Reason</th>
                                <th>Requested Date & Time</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="pendingAppointmentsBody">
                            @forelse($pendingRequests as $appointment)
                                @php
                                    $reason = $appointment->reason_for_visit ?? $appointment->reason ?? 'Regular Check-up';
                                    $reasonLower = strtolower($reason);
                                    $appointmentType = 'regular';

                                    if (strpos($reasonLower, 'emergency') !== false) {
                                        $appointmentType = 'emergency';
                                    } elseif (strpos($reasonLower, 'vaccination') !== false) {
                                        $appointmentType = 'vaccination';
                                    }
                                @endphp
                                <tr class="pending-appt-row" data-type="{{ $appointmentType }}" style="border-bottom: 1px solid #fde68a; background-color: {{ $appointmentType === 'emergency' ? '#fef2f2' : '#fffbeb' }}; {{ $appointmentType === 'emergency' ? 'border-left: 4px solid #dc2626;' : '' }}">
                                    <td style="padding: 1.25rem; color: #1f2937; font-weight: 500;">{{ $appointment->pet->name }}</td>
                                    <td style="padding: 1.25rem; color: #4b5563;">{{ $appointment->pet->species }}</td>
                                    <td style="padding: 1.25rem; color: #4b5563;">{{ $appointment->pet->owner->name }}</td>
                                    <td style="padding: 1.25rem; color: #4b5563;">{{ $appointment->pet->owner->phone ?? 'N/A' }}</td>
                                    <td style="padding: 1.25rem; color: #4b5563; font-weight: {{ $appointmentType === 'emergency' ? '600' : '500' }};">{{ $reason }}</td>
                                    <td style="padding: 1.25rem; color: #4b5563; white-space: nowrap;">{{ $appointment->appointment_date ? $appointment->appointment_date->format('M d, Y • h:i A') : 'TBD' }}</td>
                                    <td style="padding: 1.25rem; text-align: center;">
                                        <span class="status-badge warning">{{ $appointment->status }}</span>
                                    </td>
                                    <td style="padding: 1.25rem; text-align: center;">
                                        <a href="{{ route('staff.appointments.confirm', $appointment->id) }}" class="action-btn">Confirm Now</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" style="padding: 1.5rem; text-align: center; color: #6b7280;">No pending appointments awaiting confirmation</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @else
                <div style="padding: 1.5rem; color: #6b7280; text-align: center;">No pending appointments awaiting confirmation.</div>
            @endif
        </div>

        

        @if($upcomingApprovedAppointments->count() > 0)
            <div class="dashboard-panel dashboard-panel--info">
                <div class="panel-header">
                    <h3>Upcoming Approved Appointments</h3>
                    <span class="count-pill info">{{ $upcomingApprovedAppointments->count() }} confirmed</span>
                </div>
                <div class="table-shell">
                    <table class="dashboard-table">
                        <thead>
                            <tr>
                                <th>Pet Name</th>
                                <th>Species</th>
                                <th>Owner</th>
                                <th>Phone</th>
                                <th>Reason</th>
                                <th>Date & Time</th>
                                <th>Veterinarian</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($upcomingApprovedAppointments as $appointment)
                                <tr>
                                    <td style="padding: 1.25rem; color: #1f2937; font-weight: 500;">{{ $appointment->pet->name }}</td>
                                    <td style="padding: 1.25rem; color: #4b5563;">{{ $appointment->pet->species }}</td>
                                    <td style="padding: 1.25rem; color: #4b5563;">{{ $appointment->pet->owner->name }}</td>
                                    <td style="padding: 1.25rem; color: #4b5563;">{{ $appointment->pet->owner->phone ?? 'N/A' }}</td>
                                    <td style="padding: 1.25rem; color: #4b5563;">{{ $appointment->reason_for_visit ?? $appointment->reason ?? 'Regular Check-up' }}</td>
                                    <td style="padding: 1.25rem; color: #4b5563; white-space: nowrap;">{{ $appointment->appointment_date ? $appointment->appointment_date->format('M d, Y • h:i A') : 'TBD' }}</td>
                                    <td style="padding: 1.25rem; color: #4b5563;">
                                        @if($appointment->consultation && $appointment->consultation->veterinarian)
                                            Dr. {{ $appointment->consultation->veterinarian->name }}
                                        @else
                                            <span style="color: #9ca3af; font-style: italic;">Not assigned</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" style="padding: 1.5rem; text-align: center; color: #6b7280;">No upcoming approved appointments</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            <div class="dashboard-panel" style="padding: 1.25rem; color: #6b7280; text-align: center;">No upcoming approved appointments.</div>
        @endif
    </div>

    <script>
        function filterPendingAppointments(filterType) {
            const rows = document.querySelectorAll('.pending-appt-row');

            rows.forEach((row) => {
                const type = row.getAttribute('data-type');
                const matches = filterType === 'all' || type === filterType;
                row.style.display = matches ? '' : 'none';
            });

            document.querySelectorAll('.filter-btn').forEach((btn) => {
                const isActive = btn.getAttribute('data-filter') === filterType;
                btn.style.opacity = isActive ? '1' : '0.6';
                btn.style.boxShadow = isActive ? '0 0 0 2px rgba(0,0,0,0.2)' : 'none';
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            const tbody = document.getElementById('pendingAppointmentsBody');
            if (!tbody) {
                return;
            }

            const rows = Array.from(document.querySelectorAll('.pending-appt-row'));
            rows.sort((a, b) => {
                const typeA = a.getAttribute('data-type');
                const typeB = b.getAttribute('data-type');
                if (typeA === 'emergency' && typeB !== 'emergency') return -1;
                if (typeA !== 'emergency' && typeB === 'emergency') return 1;
                return 0;
            });

            rows.forEach((row) => tbody.appendChild(row));

            document.querySelectorAll('.filter-btn').forEach((btn) => {
                const isActive = btn.getAttribute('data-filter') === 'all';
                btn.style.opacity = isActive ? '1' : '0.6';
            });
        });
    </script>
@endsection
