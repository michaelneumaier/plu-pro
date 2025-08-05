<?php

namespace App\Observers;

use App\Mail\FeedbackStatusUpdated;
use App\Models\Feedback;
use Illuminate\Support\Facades\Mail;

class FeedbackObserver
{
    /**
     * Handle the Feedback "created" event.
     */
    public function created(Feedback $feedback): void
    {
        //
    }

    /**
     * Handle the Feedback "updated" event.
     */
    public function updated(Feedback $feedback): void
    {
        // Check if status was changed and user exists
        if ($feedback->isDirty('status') && $feedback->user) {
            $oldStatus = $feedback->getOriginal('status');

            // Only send email if status actually changed to something meaningful
            if ($oldStatus !== $feedback->status && $oldStatus !== null) {
                Mail::to($feedback->user->email)->send(
                    new FeedbackStatusUpdated($feedback, $oldStatus)
                );
            }
        }
    }

    /**
     * Handle the Feedback "deleted" event.
     */
    public function deleted(Feedback $feedback): void
    {
        //
    }

    /**
     * Handle the Feedback "restored" event.
     */
    public function restored(Feedback $feedback): void
    {
        //
    }

    /**
     * Handle the Feedback "force deleted" event.
     */
    public function forceDeleted(Feedback $feedback): void
    {
        //
    }
}
