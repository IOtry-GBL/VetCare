<?php

namespace App\Livewire;

use App\Models\AdherenceNotification;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\Attributes\On;

class AdherenceNotificationInbox extends Component
{
    public $notifications = [];
    public $activeTab = 'all';
    public $expandedId = null;

    public function mount()
    {
        $this->loadNotifications();
    }

    public function loadNotifications()
    {
        $user = Auth::user();
        
        $query = AdherenceNotification::where('user_id', $user->user_id)
            ->where('status', '!=', 'Deleted')
            ->orderBy('scheduled_at', 'desc')
            ->orderBy('created_at', 'desc');

        if ($this->activeTab === 'pending') {
            $query->where('status', 'Pending');
        } elseif ($this->activeTab === 'confirmed') {
            $query->where('status', 'Confirmed');
        } elseif ($this->activeTab === 'missed') {
            $query->where('status', 'Missed');
        }

        $this->notifications = $query->get()->toArray();
    }

    public function switchTab($tab)
    {
        $this->activeTab = $tab;
        $this->loadNotifications();
    }

    public function toggleExpand($notificationId)
    {
        $this->expandedId = $this->expandedId === $notificationId ? null : $notificationId;
    }

    public function deleteNotification($notificationId)
    {
        $notification = AdherenceNotification::find($notificationId);
        if ($notification && $notification->user_id === Auth::user()->user_id) {
            $notification->deleteNotification();
            $this->dispatch('notificationDeleted', ['id' => $notificationId]);
            $this->loadNotifications();
        }
    }

    public function confirmNotification($notificationId)
    {
        $notification = AdherenceNotification::find($notificationId);
        if ($notification && $notification->user_id === Auth::user()->user_id) {
            if ($notification->confirm()) {
                $this->dispatch('notificationConfirmed', ['id' => $notificationId]);
            }
            $this->loadNotifications();
        }
    }

    #[On('adherenceConfirmed')]
    public function onAdherenceConfirmed($data)
    {
        $this->loadNotifications();
    }

    public function render()
    {
        return view('livewire.adherence-notification-inbox', [
            'notifications' => $this->notifications,
            'activeTab' => $this->activeTab,
            'expandedId' => $this->expandedId,
        ]);
    }
}
