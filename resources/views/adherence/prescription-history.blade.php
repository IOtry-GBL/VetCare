@extends('layouts.app')

@section('title', 'Prescription Adherence History')

@section('sidebar')
    <a href="{{ route('pet-owner.dashboard') }}" class="sidebar-item">Dashboard</a>
    <a href="{{ route('pet-owner.prescriptions') }}" class="sidebar-item">Prescriptions</a>
    <a href="{{ route('adherence.dashboard') }}" class="sidebar-item active">Medication Adherence</a>
@endsection

@section('content')
    <section class="hero-card">
        <div class="hero-row">
            <div>
                <span class="eyebrow">Medication History</span>
                <h1 class="hero-title">{{ $prescription->medication_name }}</h1>
                <p class="hero-copy">
                    Dosage: {{ $prescription->dosage }} | Frequency: {{ $prescription->frequency }} | Duration: {{ $prescription->duration }}
                </p>
            </div>
            <div class="action-row">
                <a href="{{ route('adherence.dashboard') }}" class="btn btn-secondary">← Back to Adherence</a>
            </div>
        </div>
    </section>

    <section class="workspace-grid">
        <div class="card stack">
            <div class="surface-header">
                <span class="eyebrow">Adherence Log</span>
                <h2>Medication Intake History</h2>
            </div>

            @if($adherenceLogs->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 border-b-2 border-gray-200">
                            <tr>
                                <th class="text-left px-4 py-3 font-semibold text-gray-900">Scheduled Date</th>
                                <th class="text-left px-4 py-3 font-semibold text-gray-900">Status</th>
                                <th class="text-left px-4 py-3 font-semibold text-gray-900">Confirmed Time</th>
                                <th class="text-left px-4 py-3 font-semibold text-gray-900">Notes</th>
                                <th class="text-center px-4 py-3 font-semibold text-gray-900">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($adherenceLogs as $log)
                                @php
                                    $statusColor = match($log->intake_status) {
                                        'Taken' => 'bg-green-50',
                                        'Missed' => 'bg-red-50',
                                        'Pending' => 'bg-yellow-50',
                                        'Delayed' => 'bg-orange-50',
                                        default => 'bg-gray-50',
                                    };
                                    $statusBadge = match($log->intake_status) {
                                        'Taken' => '<span class="px-3 py-1 bg-green-200 text-green-800 rounded-full text-xs font-medium">Taken</span>',
                                        'Missed' => '<span class="px-3 py-1 bg-red-200 text-red-800 rounded-full text-xs font-medium">Missed</span>',
                                        'Pending' => '<span class="px-3 py-1 bg-yellow-200 text-yellow-800 rounded-full text-xs font-medium">Pending</span>',
                                        'Delayed' => '<span class="px-3 py-1 bg-orange-200 text-orange-800 rounded-full text-xs font-medium">Delayed</span>',
                                        default => '<span class="px-3 py-1 bg-gray-200 text-gray-800 rounded-full text-xs font-medium">Unknown</span>',
                                    };
                                @endphp
                                <tr class="{{ $statusColor }}">
                                    <td class="px-4 py-3 text-gray-900 font-medium">
                                        {{ $log->scheduled_datetime->format('M d, Y g:i A') }}
                                    </td>
                                    <td class="px-4 py-3">
                                        {!! $statusBadge !!}
                                    </td>
                                    <td class="px-4 py-3 text-gray-700">
                                        @if($log->confirmation_time)
                                            {{ $log->confirmation_time->format('M d, Y g:i A') }}
                                        @else
                                            <span class="text-gray-500">—</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-gray-700">
                                        @if($log->remarks)
                                            {{ $log->remarks }}
                                        @else
                                            <span class="text-gray-500">—</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        @if($log->notification)
                                            <a href="#" class="text-blue-600 hover:text-blue-900 font-medium text-xs">View Notification</a>
                                        @else
                                            <span class="text-gray-500 text-xs">No notification</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if($adherenceLogs->hasPages())
                    <div class="mt-4 flex justify-between items-center">
                        <div>
                            Showing {{ $adherenceLogs->firstItem() }} to {{ $adherenceLogs->lastItem() }} of {{ $adherenceLogs->total() }} records
                        </div>
                        <div>
                            {{ $adherenceLogs->links() }}
                        </div>
                    </div>
                @endif
            @else
                <div class="text-center py-8">
                    <p class="text-gray-600 font-medium">No adherence records found</p>
                    <p class="text-gray-500 text-sm">Adherence logs will appear here once medication doses are tracked</p>
                </div>
            @endif

            <!-- Summary Statistics -->
            <div class="mt-6 pt-6 border-t border-gray-200 grid grid-cols-4 gap-4">
                <div class="text-center">
                    <div class="text-2xl font-bold text-green-600">
                        {{ $adherenceLogs->where('intake_status', 'Taken')->count() }}
                    </div>
                    <p class="text-sm text-gray-600 mt-1">Taken</p>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-red-600">
                        {{ $adherenceLogs->where('intake_status', 'Missed')->count() }}
                    </div>
                    <p class="text-sm text-gray-600 mt-1">Missed</p>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-yellow-600">
                        {{ $adherenceLogs->where('intake_status', 'Pending')->count() }}
                    </div>
                    <p class="text-sm text-gray-600 mt-1">Pending</p>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-orange-600">
                        {{ $adherenceLogs->where('intake_status', 'Delayed')->count() }}
                    </div>
                    <p class="text-sm text-gray-600 mt-1">Delayed</p>
                </div>
            </div>
        </div>
    </section>
@endsection
