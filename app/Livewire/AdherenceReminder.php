<?php

namespace App\Livewire;

use App\Models\AdherenceNotification;
use App\Services\AdherenceService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class AdherenceReminder extends Component
{
    public ?AdherenceNotification $notification = null;
    public int $timeRemaining = 10800;
    public int $windowSeconds = 10800;
    public bool $showReminder = false;

    public function boot()
    {
        $this->windowSeconds = AdherenceService::confirmationWindowHours() * 3600;
        $this->timeRemaining = $this->windowSeconds;
    }

    public function mount()
    {
        $this->loadPendingNotification();
        if ($this->notification) {
            $this->calculateTimeRemaining();
            $this->showReminder = true;
        }
    }

    public function loadPendingNotification()
    {
        $user = Auth::user();
        $this->notification = AdherenceNotification::where('user_id', $user->user_id)
            ->activeWindow()
            ->orderBy('scheduled_at')
            ->first();
    }

    public function calculateTimeRemaining()
    {
        if (!$this->notification) {
            return;
        }

        $now = now();
        $deadline = $this->notification->confirmation_deadline;

        if ($now->greaterThan($deadline)) {
            $this->timeRemaining = 0;
        } else {
            $this->timeRemaining = (int) $deadline->diffInSeconds($now);
        }
    }

    public function confirmAdherence()
    {
        if ($this->notification && $this->notification->confirm()) {
            $this->dispatch('adherenceConfirmed', [
                'notification_id' => $this->notification->notification_id,
                'message' => 'Medication intake confirmed successfully!',
            ]);
            $this->showReminder = false;
            $this->notification = null;
        } else {
            $this->dispatch('adherenceConfirmationFailed', [
                'message' => 'Unable to confirm - confirmation window has expired.',
            ]);
            $this->showReminder = false;
        }

        // Load next pending notification if any
        $this->loadPendingNotification();
    }

    public function dismissReminder()
    {
        $this->showReminder = false;
    }

    public function snoozeReminder()
    {
        $this->dispatch('snoozed', [
            'notification_id' => $this->notification->notification_id,
            'minutes' => 15,
        ]);
        $this->showReminder = false;
    }

    public function render()
    {
        if ($this->notification) {
            $this->calculateTimeRemaining();
        }

        return view('livewire.adherence-reminder', [
            'notification' => $this->notification,
            'timeRemaining' => $this->timeRemaining,
            'showReminder' => $this->showReminder,
        ]);
    }
}
