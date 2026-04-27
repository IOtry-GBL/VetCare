<div class="chat-container" style="display: flex; flex-direction: column; height: 100%; position: relative;">
    <!-- Message Stream -->
    <div class="message-stream" id="message-stream" wire:poll.2s="loadMessages" style="flex: 1; overflow-y: auto; padding: 1.5rem; display: flex; flex-direction: column; gap: 0.75rem; background: #f9fafb;">
        @forelse($messages as $message)
            @php
                $sender = $message->sender;
                $isOutgoing = $sender?->user_id === $currentUserId;
                $role = $sender?->role ?? 'Clinic Team';
                $senderLabel = $isOutgoing
                    ? 'You'
                    : ($role === 'Veterinarian'
                        ? 'Dr. '.($sender?->full_name ?? 'Veterinarian')
                        : ($role === 'Staff'
                            ? ($sender?->full_name ?? 'Clinic Staff')
                            : ($sender?->full_name ?? 'Clinic Team')));
                $senderInitials = strtoupper(collect(explode(' ', $sender?->full_name ?? 'Unknown'))
                    ->filter()
                    ->take(2)
                    ->map(fn ($segment) => substr($segment, 0, 1))
                    ->implode(''));
            @endphp

            <div wire:key="msg-{{ $message->message_id }}" style="display: flex; justify-content: {{ $isOutgoing ? 'flex-end' : 'flex-start' }}; margin-bottom: 0.5rem; gap: 0.75rem;">
                @if(!$isOutgoing)
                    <div style="width: 32px; height: 32px; border-radius: 50%; background: linear-gradient(135deg, var(--shell-accent), var(--shell-accent-deep)); color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.75rem; flex-shrink: 0;">
                        {{ $senderInitials ?: 'CT' }}
                    </div>
                @endif

                <div style="max-width: 65%; display: flex; flex-direction: column; {{ $isOutgoing ? 'align-items: flex-end' : 'align-items: flex-start' }};">
                    <small style="font-size: 0.75rem; color: #6b7280; margin-bottom: 0.25rem; font-weight: 500;">{{ $senderLabel }}</small>
                    <div style="padding: 0.75rem 1rem; border-radius: 0.75rem; background: {{ $isOutgoing ? '#3b82f6' : '#fff' }}; border: {{ $isOutgoing ? 'none' : '1px solid #e5e7eb' }}; color: {{ $isOutgoing ? '#fff' : '#000' }}; word-wrap: break-word; white-space: pre-wrap; word-break: break-word; line-height: 1.4;">
                        {{ $message->message_body }}
                    </div>
                    <small style="font-size: 0.7rem; color: #9ca3af; margin-top: 0.25rem;">{{ $message->created_at?->format('M d g:i A') }}</small>
                </div>

                @if($isOutgoing)
                    <div style="width: 32px; height: 32px; border-radius: 50%; background: linear-gradient(135deg, var(--shell-accent), var(--shell-accent-deep)); color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.75rem; flex-shrink: 0;">
                        You
                    </div>
                @endif
            </div>
        @empty
            <div style="display: flex; align-items: center; justify-content: center; height: 100%; color: #9ca3af; text-align: center;">
                <div>
                    <p style="margin-bottom: 0.5rem; font-size: 0.95rem;">No messages yet</p>
                    <p style="font-size: 0.875rem;">Start the conversation by sending a message below.</p>
                </div>
            </div>
        @endforelse
    </div>

    <!-- Message Composer -->
    <form wire:submit.prevent="sendMessage" style="border-top: 1px solid #e5e7eb; padding: 1rem; background: #fff; flex-shrink: 0;">
        <div style="display: flex; gap: 0.75rem; align-items: flex-end;">
            <div style="flex: 1; min-width: 0;">
                <textarea
                    id="messageBody"
                    wire:model.debounce.500ms="messageBody"
                    class="field-control"
                    placeholder="Type a message... (Ctrl+Enter to send)"
                    rows="2"
                    wire:keydown.enter.ctrl="sendMessage"
                    style="resize: none; font-family: inherit; font-size: 0.95rem; width: 100%;"
                ></textarea>
                @error('messageBody')
                    <span style="color: #ef4444; font-size: 0.875rem; display: block; margin-top: 0.25rem;">{{ $message }}</span>
                @enderror
            </div>
            <button 
                type="submit" 
                class="btn btn-primary"
                wire:loading.attr="disabled"
                wire:loading.class="opacity-50"
                style="padding: 0.75rem 1.5rem; height: fit-content; white-space: nowrap; background: linear-gradient(135deg, var(--shell-accent), var(--shell-accent-deep)); color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 600; flex-shrink: 0;"
            >
                <span wire:loading.remove>Send</span>
                <span wire:loading>Loading...</span>
            </button>
        </div>
        <small style="display: block; margin-top: 0.5rem; color: #9ca3af; font-size: 0.75rem;">Press Ctrl+Enter to send message</small>
    </form>

    <script>
        // Auto-scroll to latest message when new messages arrive
        Livewire.on('messageAdded', () => {
            setTimeout(() => {
                const messageStream = document.getElementById('message-stream');
                if (messageStream) {
                    messageStream.scrollTop = messageStream.scrollHeight;
                }
            }, 100);
        });

        // Scroll on component init
        document.addEventListener('DOMContentLoaded', () => {
            setTimeout(() => {
                const messageStream = document.getElementById('message-stream');
                if (messageStream) {
                    messageStream.scrollTop = messageStream.scrollHeight;
                }
            }, 100);
        });

        // Scroll when Livewire updates (for polling)
        document.addEventListener('livewire:updated', () => {
            setTimeout(() => {
                const messageStream = document.getElementById('message-stream');
                if (messageStream) {
                    const isNearBottom = messageStream.scrollHeight - messageStream.scrollTop - messageStream.clientHeight < 100;
                    if (isNearBottom) {
                        messageStream.scrollTop = messageStream.scrollHeight;
                    }
                }
            }, 50);
        });

        // Auto-focus textarea
        document.addEventListener('livewire:updated', () => {
            const textarea = document.getElementById('messageBody');
            if (textarea && textarea.value === '') {
                textarea.focus();
            }
        });
    </script>
</div>
