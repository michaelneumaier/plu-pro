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

    public function render()
    {
        $userLists = Auth::user()->userLists;

        return view('livewire.lists.index', [
            'userLists' => $userLists,
        ]);
    }
}
