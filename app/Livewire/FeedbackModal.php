<?php

namespace App\Livewire;

use App\Mail\FeedbackSubmitted;
use App\Models\Feedback;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Livewire\Attributes\Validate;
use Livewire\Component;

class FeedbackModal extends Component
{
    #[Validate('required|in:bug,feature,improvement,general')]
    public string $type = 'general';

    #[Validate('required|string|max:255')]
    public string $subject = '';

    #[Validate('required|string|max:5000')]
    public string $message = '';

    public bool $showModal = false;

    public string $currentUrl = '';

    protected $listeners = [
        'openFeedbackModal' => 'openModal',
        'closeFeedbackModal' => 'closeModal',
    ];

    public function openModal($data = [])
    {
        $this->type = $data['type'] ?? 'general';
        $this->subject = $data['subject'] ?? '';
        $this->currentUrl = $data['url'] ?? '';
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->reset(['type', 'subject', 'message', 'currentUrl']);
        $this->showModal = false;
        $this->resetValidation();
    }

    public function submitFeedback()
    {
        $this->validate();

        // Collect metadata
        $metadata = [
            'page_url' => $this->currentUrl ?: (request()->header('referer') ?? url()->current()),
            'user_agent' => request()->header('user-agent'),
            'ip_address' => request()->ip(),
            'timestamp' => now()->toISOString(),
            'screen_resolution' => null, // Will be populated by JavaScript if available
        ];

        // Create feedback record
        $feedback = Feedback::create([
            'user_id' => auth()->id(),
            'type' => $this->type,
            'subject' => $this->subject,
            'message' => $this->message,
            'metadata' => $metadata,
        ]);

        // Send email notification to admins
        $admins = User::where('role', 'admin')->get();
        foreach ($admins as $admin) {
            Mail::to($admin->email)->send(new FeedbackSubmitted($feedback));
        }

        // Show success message
        $this->dispatch('banner-message', [
            'style' => 'success',
            'message' => 'Thank you for your feedback! We\'ll review it shortly.',
        ]);

        // Close modal and reset form
        $this->closeModal();
    }

    public function render()
    {
        return view('livewire.feedback-modal');
    }
}
