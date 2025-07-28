<?php

namespace App\Livewire\Lists;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\UserList;

class Index extends Component
{
    // Share functionality
    public $showShareModal = false;
    public $selectedList = null;
    public $isPublic = false;
    public $shareUrl = '';

    // Create list functionality
    public $showCreateModal = false;
    public $newListName = '';

    // Delete list functionality
    public $showDeleteModal = false;
    public $listToDelete = null;

    // Publish to marketplace functionality
    public $showPublishModal = false;
    public $listToPublish = null;
    public $marketplaceTitle = '';
    public $marketplaceDescription = '';
    public $marketplaceCategory = '';
    
    // Unpublish functionality
    public $showUnpublishModal = false;
    public $listToUnpublish = null;

    protected $rules = [
        'newListName' => 'required|string|max:255',
        'marketplaceTitle' => 'required|string|max:255',
        'marketplaceDescription' => 'nullable|string|max:1000',
        'marketplaceCategory' => 'nullable|string|max:50',
    ];

    public function toggleShareModal($listId = null)
    {
        if ($listId) {
            $this->selectedList = UserList::findOrFail($listId);
            $this->isPublic = $this->selectedList->is_public;
            $this->shareUrl = $this->selectedList->share_url ?? '';
        }
        
        $this->showShareModal = !$this->showShareModal;
    }

    public function togglePublicSharing()
    {
        if (!$this->selectedList) return;

        $this->selectedList->update([
            'is_public' => !$this->selectedList->is_public
        ]);
        
        if (!$this->selectedList->share_code) {
            $this->selectedList->generateNewShareCode();
        }
        
        $this->selectedList->refresh();
        
        // Update reactive properties
        $this->isPublic = $this->selectedList->is_public;
        $this->shareUrl = $this->selectedList->share_url ?? '';
    }

    public function toggleCreateModal()
    {
        $this->showCreateModal = !$this->showCreateModal;
        $this->newListName = ''; // Clear the input when opening/closing
        $this->resetErrorBag(); // Clear any validation errors
    }

    public function createList()
    {
        $this->validate();

        $list = Auth::user()->userLists()->create([
            'name' => $this->newListName,
        ]);

        $this->toggleCreateModal(); // Close modal
        
        // Redirect to the new list
        return redirect()->route('lists.show', $list);
    }

    public function confirmDelete($listId)
    {
        $this->listToDelete = UserList::findOrFail($listId);
        $this->showDeleteModal = true;
    }

    public function deleteList()
    {
        if ($this->listToDelete) {
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

    public function togglePublishModal($listId = null)
    {
        if ($listId) {
            $this->listToPublish = UserList::findOrFail($listId);
            $this->marketplaceTitle = $this->listToPublish->name;
            $this->marketplaceDescription = '';
            $this->marketplaceCategory = '';
        }
        
        $this->showPublishModal = !$this->showPublishModal;
        
        if (!$this->showPublishModal) {
            $this->resetPublishForm();
        }
    }

    public function publishToMarketplace()
    {
        $this->validate([
            'marketplaceTitle' => 'required|string|max:255',
            'marketplaceDescription' => 'nullable|string|max:1000',
            'marketplaceCategory' => 'nullable|string|max:50',
        ]);

        if (!$this->listToPublish) return;

        $this->listToPublish->update([
            'marketplace_enabled' => true,
            'marketplace_title' => $this->marketplaceTitle,
            'marketplace_description' => $this->marketplaceDescription ?: null,
            'marketplace_category' => $this->marketplaceCategory ?: null,
            'published_at' => now(),
        ]);
        
        // Generate share code if it doesn't exist
        if (!$this->listToPublish->share_code) {
            $this->listToPublish->generateNewShareCode();
        }
        
        $this->togglePublishModal();
        session()->flash('message', 'List published to marketplace successfully!');
    }

    protected function resetPublishForm()
    {
        $this->listToPublish = null;
        $this->marketplaceTitle = '';
        $this->marketplaceDescription = '';
        $this->marketplaceCategory = '';
        $this->resetErrorBag();
    }
    
    public function confirmUnpublish($listId)
    {
        $this->listToUnpublish = UserList::findOrFail($listId);
        $this->showUnpublishModal = true;
    }
    
    public function unpublishFromMarketplace()
    {
        if (!$this->listToUnpublish) return;
        
        $this->listToUnpublish->update([
            'marketplace_enabled' => false,
            'marketplace_title' => null,
            'marketplace_description' => null,
            'marketplace_category' => null,
            'published_at' => null,
        ]);
        
        $this->showUnpublishModal = false;
        $this->listToUnpublish = null;
        
        session()->flash('message', 'List unpublished from marketplace successfully!');
    }
    
    public function cancelUnpublish()
    {
        $this->showUnpublishModal = false;
        $this->listToUnpublish = null;
    }

    public function render()
    {
        $userLists = Auth::user()->userLists;

        return view('livewire.lists.index', [
            'userLists' => $userLists,
        ]);
    }
}
