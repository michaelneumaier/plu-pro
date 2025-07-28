<?php

namespace App\Livewire;

use App\Models\PLUCode;
use App\Models\User;
use App\Models\UserList;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;

class About extends Component
{
    public $stats = [];

    public function mount()
    {
        $this->loadStats();
    }

    protected function loadStats()
    {
        $this->stats = Cache::remember('about_page_stats', 3600, function () {
            return [
                'total_plus' => PLUCode::count(),
                'total_users' => User::count(),
                'total_lists' => UserList::count(),
                'commodities' => PLUCode::distinct('commodity')->count('commodity'),
            ];
        });
    }

    public function render()
    {
        return view('livewire.about')
            ->layout('layouts.app')
            ->title('About PLU Pro - Professional Produce Code Management Platform');
    }
}
