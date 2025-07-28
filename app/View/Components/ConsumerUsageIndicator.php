<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class ConsumerUsageIndicator extends Component
{
    public $tier;

    /**
     * Create a new component instance.
     */
    public function __construct($tier)
    {
        $this->tier = strtolower($tier); // Ensure consistency
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.consumer-usage-indicator');
    }
}
