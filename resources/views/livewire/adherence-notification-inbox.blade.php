<div class="card stack">
    <div class="surface-header">
        <div>
            <span class="eyebrow">Medication History</span>
            <h2>Adherence Notifications</h2>
        </div>
    </div>

    <!-- Tab Navigation -->
    <div class="flex gap-2 border-b border-gray-200 mb-4 -mx-4 px-4">
        <button
            wire:click="switchTab('all')"
            class="px-4 py-3 font-medium transition-colors {{ $activeTab === 'all' ? 'border-b-2 border-blue-600 text-blue-600' : 'text-gray-600 hover:text-gray-900' }}"
        >
            All
        </button>
        <button
            wire:click="switchTab('pending')"
            class="px-4 py-3 font-medium transition-colors {{ $activeTab === 'pending' ? 'border-b-2 border-blue-600 text-blue-600' : 'text-gray-600 hover:text-gray-900' }}"
        >
            Pending
        </button>
        <button
            wire:click="switchTab('confirmed')"
            class="px-4 py-3 font-medium transition-colors {{ $activeTab === 'confirmed' ? 'border-b-2 border-blue-600 text-blue-600' : 'text-gray-600 hover:text-gray-900' }}"
        >
            Confirmed
        </button>
        <button
            wire:click="switchTab('missed')"
            class="px-4 py-3 font-medium transition-colors {{ $activeTab === 'missed' ? 'border-b-2 border-blue-600 text-blue-600' : 'text-gray-600 hover:text-gray-900' }}"
        >
            Missed
        </button>
    </div>

    <!-- Notifications List -->
    @if(count($notifications) > 0)
        <div class="space-y-3 max-h-96 overflow-y-auto">
            @foreach($notifications as $notif)
                @php
                    $notification = (object)$notif;
                    $isExpanded = $expandedId === $notification->notification_id;
                    $scheduledAt = \Carbon\Carbon::parse($notification->scheduled_at)->timezone(\App\Services\AdherenceService::clinicTimezone());
                    $deadlineAt = \Carbon\Carbon::parse($notification->confirmation_deadline)->timezone(\App\Services\AdherenceService::clinicTimezone());
                    $isUpcoming = \Carbon\Carbon::now(\App\Services\AdherenceService::clinicTimezone())->lt($scheduledAt);
                    $statusColor = match($notification->status) {
                        'Pending' => 'bg-yellow-50 border-yellow-200',
                        'Confirmed' => 'bg-green-50 border-green-200',
                        'Missed' => 'bg-red-50 border-red-200',
                        default => 'bg-gray-50 border-gray-200',
                    };
                    $statusIcon = match($notification->status) {
                        'Pending' => 'Pending',
                        'Confirmed' => 'Confirmed',
                        'Missed' => 'Missed',
                        default => '•',
                    };
                @endphp
                
                <div class="border {{ $statusColor }} rounded-lg p-4 transition-all">
                    <!-- Summary Row -->
                    <div
                        wire:click="toggleExpand({{ $notification->notification_id }})"
                        class="cursor-pointer flex items-center justify-between gap-3"
                    >
                        <div class="flex items-center gap-3 flex-1">
                            <span class="text-2xl">{{ $statusIcon }}</span>
                            <div class="flex-1">
                                <div class="font-semibold text-gray-900">{{ $notification->medication_name }}</div>
                                <div class="text-sm text-gray-600">
                                    {{ $scheduledAt->format('M d, Y g:i A') }} PH
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="px-3 py-1 rounded-full text-xs font-medium {{ match($notification->status) {
                                'Pending' => 'bg-yellow-100 text-yellow-800',
                                'Confirmed' => 'bg-green-100 text-green-800',
                                'Missed' => 'bg-red-100 text-red-800',
                                default => 'bg-gray-100 text-gray-800',
                            } }}">
                                {{ $notification->status }}
                            </span>
                            <svg class="w-5 h-5 text-gray-400 transition-transform {{ $isExpanded ? 'transform rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
                            </svg>
                        </div>
                    </div>

                    <!-- Expanded Details -->
                    @if($isExpanded)
                        <div class="mt-4 pt-4 border-t border-gray-300 space-y-3">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <span class="text-sm text-gray-600">Dosage</span>
                                    <div class="font-semibold text-gray-900">{{ $notification->dosage }}</div>
                                </div>
                                <div>
                                    <span class="text-sm text-gray-600">Status</span>
                                    <div class="font-semibold text-gray-900">{{ $notification->status }}</div>
                                </div>
                            </div>

                            @if($notification->status === 'Pending')
                                <div class="bg-amber-50 border border-amber-200 rounded p-3">
                                    <p class="text-sm text-amber-800">
                                        <strong>Scheduled:</strong> {{ $scheduledAt->format('g:i A') }} PH
                                    </p>
                                    <p class="text-sm text-amber-800 mt-1">
                                        <strong>Deadline:</strong> {{ $deadlineAt->format('g:i A') }} PH
                                        @if($deadlineAt->isPast())
                                            <span class="text-red-600 ml-2">(Expired)</span>
                                        @elseif($isUpcoming)
                                            <span class="text-blue-600 ml-2">(Confirmation opens at the scheduled time)</span>
                                        @else
                                            <span class="text-green-600 ml-2">({{ $deadlineAt->diffForHumans() }})</span>
                                        @endif
                                    </p>
                                </div>

                                <div class="flex gap-2">
                                    @if($isUpcoming)
                                        <button
                                            type="button"
                                            disabled
                                            class="flex-1 px-3 py-2 bg-gray-200 text-gray-500 text-sm font-medium rounded cursor-not-allowed"
                                        >
                                            Available at {{ $scheduledAt->format('g:i A') }}
                                        </button>
                                    @else
                                        <button
                                            wire:click="confirmNotification({{ $notification->notification_id }})"
                                            class="flex-1 px-3 py-2 bg-green-600 text-white text-sm font-medium rounded hover:bg-green-700 transition-colors"
                                        >
                                            Confirm Taken
                                        </button>
                                    @endif
                                    <button
                                        wire:click="deleteNotification({{ $notification->notification_id }})"
                                        class="flex-1 px-3 py-2 bg-gray-200 text-gray-700 text-sm font-medium rounded hover:bg-gray-300 transition-colors"
                                    >
                                        Delete
                                    </button>
                                </div>
                            @elseif($notification->status === 'Confirmed')
                                <div class="bg-green-50 border border-green-200 rounded p-3">
                                    <p class="text-sm text-green-800">
                                        <strong>Confirmed at:</strong> {{ \Carbon\Carbon::parse($notification->confirmed_at)->timezone(\App\Services\AdherenceService::clinicTimezone())->format('M d, Y g:i A') }} PH
                                    </p>
                                </div>

                                <button
                                    wire:click="deleteNotification({{ $notification->notification_id }})"
                                    class="w-full px-3 py-2 bg-red-600 text-white text-sm font-medium rounded hover:bg-red-700 transition-colors"
                                >
                                    Delete
                                </button>
                            @elseif($notification->status === 'Missed')
                                <div class="bg-red-50 border border-red-200 rounded p-3">
                                    <p class="text-sm text-red-800">
                                        This medication dose was not confirmed within the time limit.
                                    </p>
                                </div>

                                <button
                                    wire:click="deleteNotification({{ $notification->notification_id }})"
                                    class="w-full px-3 py-2 bg-red-600 text-white text-sm font-medium rounded hover:bg-red-700 transition-colors"
                                >
                                    Delete
                                </button>
                            @endif

                            @if($notification->notes)
                                <div>
                                    <span class="text-sm text-gray-600">Notes</span>
                                    <p class="text-gray-900 text-sm mt-1">{{ $notification->notes }}</p>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @else
        <div class="text-center py-8">
            <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            <p class="text-gray-600 font-medium">No {{ $activeTab !== 'all' ? $activeTab : 'medication' }} notifications</p>
            <p class="text-gray-500 text-sm mt-1">Your medication adherence reminders will appear here</p>
        </div>
    @endif

    <!-- Loading State -->
    <div wire:loading class="absolute inset-0 bg-white bg-opacity-75 rounded-lg flex items-center justify-center">
        <div class="animate-spin">
            <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 2m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
        </div>
    </div>
</div>
