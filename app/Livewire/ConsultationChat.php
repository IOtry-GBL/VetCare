<?php

namespace App\Livewire;

use App\Models\Consultation;
use App\Models\ConsultationMessage;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\Attributes\Validate;

class ConsultationChat extends Component
{
    public Consultation $consultation;
    
    #[Validate('required|string|max:5000', message: ['required' => 'Message cannot be empty', 'max' => 'Message must not exceed 5000 characters'])]
    public string $messageBody = '';

    public function mount(Consultation $consultation)
    {
        $this->consultation = $consultation;
        $this->checkConsultationAccess($consultation);
        // Load fresh data to ensure we have latest messages
        $this->consultation = $consultation->fresh();
    }

    private function checkConsultationAccess(Consultation $consultation)
    {
        $user = Auth::user();
        
        if ($user->role === 'Pet Owner') {
            // Pet owner can only access consultations for their pets
            abort_unless(
                $consultation->appointment?->pet?->user_id === $user->user_id,
                403,
                'You are not authorized to access this consultation.'
            );
        } elseif ($user->role === 'Veterinarian') {
            // Vet can only access their own consultations
            abort_unless(
                $consultation->veterinarian_id === $user->user_id,
                403,
                'You are not authorized to access this consultation.'
            );
        } elseif ($user->role !== 'Staff') {
            // Staff can access all, otherwise deny
            abort(403, 'Insufficient permissions.');
        }
    }

    public function sendMessage()
    {
        $this->validate();

        $user = Auth::user();
        
        // Re-validate authorization
        $this->checkConsultationAccess($this->consultation);

        ConsultationMessage::create([
            'consultation_id' => $this->consultation->consultation_id,
            'sender_id' => $user->user_id,
            'message_body' => trim($this->messageBody),
        ]);

        $this->messageBody = '';
        
        // Refresh the consultation with latest messages
        $this->consultation->refresh();
        $this->dispatch('messageAdded');
    }

    public function loadMessages()
    {
        // Refresh messages from database to get latest
        $this->consultation->refresh();
    }

    public function render()
    {
        $currentUserId = Auth::user()->user_id;
        $messages = $this->consultation->messages()
            ->with('sender')
            ->orderBy('created_at', 'asc')
            ->get();
        
        return view('livewire.consultation-chat', [
            'messages' => $messages,
            'currentUserId' => $currentUserId,
        ]);
    }
}
