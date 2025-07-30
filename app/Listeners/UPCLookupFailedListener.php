<?php

namespace App\Listeners;

use App\Events\UPCLookupFailed;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class UPCLookupFailedListener
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
    public function handle(UPCLookupFailed $event): void
    {
        // For now, we'll just log the event. The component will use direct event handling.
        logger('UPC lookup failed for user ' . $event->userId . ', UPC: ' . $event->upc . ', Error: ' . $event->errorMessage);
    }
}
