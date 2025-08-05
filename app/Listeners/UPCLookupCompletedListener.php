<?php

namespace App\Listeners;

use App\Events\UPCLookupCompleted;

class UPCLookupCompletedListener
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(UPCLookupCompleted $event): void
    {
        // For now, we'll just log the event. The component will use direct event handling.
        // In a real application, you might use broadcasting or other methods
        logger('UPC lookup completed for user '.$event->userId.', UPC: '.$event->upcCode->upc);
    }
}
