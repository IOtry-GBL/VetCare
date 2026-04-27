<div class="fixed inset-0 z-50 flex items-center justify-center" wire:key="adherence-reminder">
    @if($showReminder && $notification)
        <div class="fixed inset-0 bg-black bg-opacity-50" wire:click="dismissReminder"></div>
        
        <div class="relative bg-white rounded-lg shadow-2xl max-w-md w-full mx-4 p-6 animate-slideIn">
            <!-- Close Button -->
            <button 
                wire:click="dismissReminder"
                class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 transition-colors"
            >
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>

            <!-- Header -->
            <div class="mb-4">
                <div class="flex items-center gap-3 mb-2">
                    <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 2m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">Medication Reminder</h3>
                        <p class="text-sm text-gray-600">Time to take your pet's medication</p>
                    </div>
                </div>
            </div>

            <!-- Medication Details -->
            <div class="bg-blue-50 rounded-lg p-4 mb-4">
                <div class="space-y-2">
                    <div class="flex justify-between">
                        <span class="text-gray-700 font-medium">Medication:</span>
                        <span class="text-gray-900 font-semibold">{{ $notification->medication_name }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-700 font-medium">Dosage:</span>
                        <span class="text-gray-900 font-semibold">{{ $notification->dosage }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-700 font-medium">Scheduled:</span>
                        <span class="text-gray-900 font-semibold">{{ $notification->scheduledAtInClinicTimezone()?->format('g:i A') }} PH</span>
                    </div>
                </div>
            </div>

            <!-- Time Remaining Indicator -->
            <div class="mb-4">
                <div class="flex justify-between items-center mb-2">
                    <span class="text-sm font-medium text-gray-700">Time to confirm:</span>
                    <span class="text-sm font-bold" :class="$timeRemaining < 600 ? 'text-red-600' : 'text-green-600'">
                        {{ \Carbon\Carbon::now()->addSeconds($timeRemaining)->format('H:i:s') }}
                    </span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div 
                        class="bg-blue-600 h-2 rounded-full transition-all"
                        style="width: {{ max(0, ($timeRemaining / max(1, $windowSeconds)) * 100) }}%"
                    ></div>
                </div>
                <p class="text-xs text-gray-600 mt-2">
                    @if($timeRemaining > 600)
                        You have {{ \Carbon\Carbon::now()->addSeconds($timeRemaining)->diff(now())->format('%h hours %i minutes') }} to confirm
                    @elseif($timeRemaining > 60)
                        <span class="text-orange-600 font-medium">Warning: Only {{ \Carbon\Carbon::now()->addSeconds($timeRemaining)->diff(now())->format('%i minutes') }} remaining!</span>
                    @else
                        <span class="text-red-600 font-medium">Urgent: Less than 1 minute left!</span>
                    @endif
                </p>
            </div>

            <!-- Warning Message -->
            <div class="bg-amber-50 border-l-4 border-amber-400 p-3 mb-4 rounded">
                <p class="text-sm text-amber-800">
                    <strong>Important:</strong> If you don't confirm within the time limit, it will be marked as missed.
                </p>
            </div>

            <!-- Action Buttons -->
            <div class="flex gap-3">
                <button
                    wire:click="snoozeReminder"
                    class="flex-1 px-4 py-2 text-gray-700 font-medium border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
                >
                    Remind me later
                </button>
                <button
                    wire:click="confirmAdherence"
                    class="flex-1 px-4 py-2 bg-green-600 text-white font-medium rounded-lg hover:bg-green-700 transition-colors"
                >
                    Confirm Taken
                </button>
            </div>

            <!-- Loading State -->
            <div wire:loading class="absolute inset-0 bg-white bg-opacity-75 rounded-lg flex items-center justify-center">
                <div class="flex flex-col items-center gap-2">
                    <div class="animate-spin">
                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 2m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <span class="text-sm text-gray-600">Processing...</span>
                </div>
            </div>
        </div>

        <style>
            @keyframes slideIn {
                from {
                    transform: scale(0.95) translateY(-20px);
                    opacity: 0;
                }
                to {
                    transform: scale(1) translateY(0);
                    opacity: 1;
                }
            }

            .animate-slideIn {
                animation: slideIn 0.3s ease-out;
            }
        </style>
    @endif
</div>
