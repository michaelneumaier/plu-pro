<?php

namespace App\Events;

use App\Models\UPCCode;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UPCLookupCompleted
{
    use Dispatchable, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public int $userId,
        public UPCCode $upcCode
    ) {
        //
    }
}
