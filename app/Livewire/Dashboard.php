<?php

namespace App\Livewire;

use App\Services\ActivityTracker;
use App\Services\DashboardAnalytics;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Dashboard extends Component
{
    public $userStats = [];

    public $recentLists = [];

    public $recentActivity = [];

    public $pluInsights = [];

    public $marketplaceInsights = [];

    public $sharingInsights = [];

    // Create list functionality
    public $showCreateModal = false;

    public $newListName = '';

    protected $analyticsService;

    protected $activityTracker;

    protected $rules = [
        'newListName' => 'required|string|max:255',
    ];

    public function boot(DashboardAnalytics $analyticsService, ActivityTracker $activityTracker)
    {
        $this->analyticsService = $analyticsService;
        $this->activityTracker = $activityTracker;
    }

    public function mount()
    {
        $this->loadDashboardData();
    }

    public function loadDashboardData()
    {
        $user = Auth::user();

        // Load all dashboard sections
        $this->userStats = $this->analyticsService->getUserStats($user);
        $this->recentLists = $this->analyticsService->getRecentLists($user, 5);
        $this->recentActivity = $this->activityTracker->getRecentActivity($user, 8);
        $this->pluInsights = $this->analyticsService->getPLUInsights($user);
        $this->marketplaceInsights = $this->analyticsService->getMarketplaceInsights($user);
        $this->sharingInsights = $this->analyticsService->getSharingInsights($user);
    }

    public function refreshData()
    {
        // Clear cache and reload
        $this->analyticsService->clearUserCache();
        $this->loadDashboardData();

        session()->flash('message', 'Dashboard data refreshed!');
    }

    public function toggleCreateModal()
    {
        $this->showCreateModal = ! $this->showCreateModal;
        $this->newListName = ''; // Clear the input when opening/closing
        $this->resetErrorBag(); // Clear any validation errors
    }

    public function createList()
    {
        $this->validate();

        $list = Auth::user()->userLists()->create([
            'name' => $this->newListName,
        ]);

        // Track activity
        $this->activityTracker->log(ActivityTracker::ACTION_CREATED_LIST, $list);

        $this->toggleCreateModal(); // Close modal

        // Redirect to the new list
        return redirect()->route('lists.show', $list);
    }

    public function render()
    {
        return view('livewire.dashboard')->layout('layouts.app');
    }
}
