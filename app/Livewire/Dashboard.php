<?php

namespace App\Livewire;

use App\Models\UserList;
use App\Services\ActivityTracker;
use App\Services\DashboardAnalytics;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Dashboard extends Component
{
    use WithPagination;

    public $userStats = [];

    public $recentActivity = [];

    public $pluInsights = [];

    public $marketplaceInsights = [];

    public $sharingInsights = [];

    // Create list functionality
    public $showCreateModal = false;

    public $newListName = '';

    // Share functionality
    public $showShareModal = false;

    public $selectedList = null;

    public $isPublic = false;

    public $shareUrl = '';

    // Rename functionality
    public $showRenameModal = false;

    public $listToRename = null;

    public $renameValue = '';

    // Delete functionality
    public $showDeleteModal = false;

    public $listToDelete = null;

    // Default list (PWA home)
    public $defaultListId = null;

    protected $analyticsService;

    protected $activityTracker;

    protected $rules = [
        'newListName' => 'required|string|max:255',
        'renameValue' => 'required|string|max:255',
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

        $this->userStats = $this->analyticsService->getUserStats($user);
        $this->recentActivity = $this->activityTracker->getRecentActivity($user, 8);
        $this->pluInsights = $this->analyticsService->getPLUInsights($user);
        $this->marketplaceInsights = $this->analyticsService->getMarketplaceInsights($user);
        $this->sharingInsights = $this->analyticsService->getSharingInsights($user);
    }

    public function refreshData()
    {
        $this->analyticsService->clearUserCache();
        $this->loadDashboardData();

        session()->flash('message', 'Dashboard data refreshed!');
    }

    // --- Create list ---

    public function toggleCreateModal()
    {
        $this->showCreateModal = ! $this->showCreateModal;
        $this->newListName = '';
        $this->resetErrorBag();
    }

    public function createList()
    {
        $this->validate(['newListName' => 'required|string|max:255']);

        $list = Auth::user()->userLists()->create([
            'name' => $this->newListName,
        ]);

        $this->activityTracker->log(ActivityTracker::ACTION_CREATED_LIST, $list);

        $this->toggleCreateModal();

        return redirect()->route('lists.show', $list);
    }

    // --- Share list ---

    public function toggleShareModal($listId = null)
    {
        if ($listId) {
            $this->selectedList = UserList::findOrFail($listId);
            $this->isPublic = $this->selectedList->is_public;
            $this->shareUrl = $this->selectedList->share_url ?? '';
        }

        $this->showShareModal = ! $this->showShareModal;
    }

    public function togglePublicSharing()
    {
        if (! $this->selectedList) {
            return;
        }

        $this->selectedList->update([
            'is_public' => ! $this->selectedList->is_public,
        ]);

        if (! $this->selectedList->share_code) {
            $this->selectedList->generateNewShareCode();
        }

        $this->selectedList->refresh();

        $this->isPublic = $this->selectedList->is_public;
        $this->shareUrl = $this->selectedList->share_url ?? '';
    }

    // --- Rename list ---

    public function openRenameModal($listId)
    {
        $this->listToRename = UserList::findOrFail($listId);
        $this->renameValue = $this->listToRename->name;
        $this->showRenameModal = true;
        $this->resetErrorBag();
    }

    public function saveRename()
    {
        $this->validate(['renameValue' => 'required|string|max:255']);

        if ($this->listToRename) {
            $this->listToRename->update(['name' => $this->renameValue]);

            session()->flash('message', 'List renamed successfully!');
        }

        $this->showRenameModal = false;
        $this->listToRename = null;
        $this->renameValue = '';
    }

    public function cancelRename()
    {
        $this->showRenameModal = false;
        $this->listToRename = null;
        $this->renameValue = '';
        $this->resetErrorBag();
    }

    // --- Delete list ---

    public function confirmDelete($listId)
    {
        $this->listToDelete = UserList::findOrFail($listId);
        $this->showDeleteModal = true;
    }

    public function deleteList()
    {
        if ($this->listToDelete) {
            $this->activityTracker->log(
                ActivityTracker::ACTION_DELETED_LIST,
                null,
                ['list_name' => $this->listToDelete->name]
            );

            $this->listToDelete->delete();
            $this->showDeleteModal = false;
            $this->listToDelete = null;

            session()->flash('message', 'List deleted successfully!');
        }
    }

    public function cancelDelete()
    {
        $this->showDeleteModal = false;
        $this->listToDelete = null;
    }

    // --- Default list (PWA home) ---

    public function setDefaultList($listId)
    {
        $this->defaultListId = $listId;
    }

    public function clearDefaultList()
    {
        $this->defaultListId = null;
    }

    public function render()
    {
        $query = Auth::user()->userLists()->withCount('listItems');

        if ($this->defaultListId) {
            $query->orderByRaw('CASE WHEN id = ? THEN 0 ELSE 1 END, updated_at DESC', [$this->defaultListId]);
        } else {
            $query->orderBy('updated_at', 'desc');
        }

        $lists = $query->paginate(5);

        return view('livewire.dashboard', [
            'lists' => $lists,
        ])->layout('layouts.app');
    }
}
