<?php

namespace App\Providers;

use App\Events\UPCLookupCompleted;
use App\Events\UPCLookupFailed;
use App\Listeners\UPCLookupCompletedListener;
use App\Listeners\UPCLookupFailedListener;
use App\Models\Feedback;
use App\Observers\FeedbackObserver;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register UPC lookup event listeners
        Event::listen(UPCLookupCompleted::class, UPCLookupCompletedListener::class);
        Event::listen(UPCLookupFailed::class, UPCLookupFailedListener::class);

        // Register model observers
        Feedback::observe(FeedbackObserver::class);
    }
}
