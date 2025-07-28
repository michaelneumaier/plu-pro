<?php

namespace App\Livewire\Lists;

use App\Models\UserList;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class SharedView extends Component
{
    public UserList $userList;

    public Collection $listItems;

    public Collection $allListItems; // For copying - includes items with 0 inventory

    // Copy functionality
    public bool $showCopyModal = false;

    public string $customListName = '';

    public function mount($shareCode)
    {
        // Find the list by share code and ensure it's public
        $this->userList = UserList::where('share_code', $shareCode)
            ->where('is_public', true)
            ->firstOrFail();

        $this->loadListItems();
    }

    protected function loadListItems()
    {
        // Load ALL items for copying purposes
        $this->allListItems = $this->userList->listItems()
            ->with(['pluCode'])
            ->join('plu_codes', 'list_items.plu_code_id', '=', 'plu_codes.id')
            ->orderBy('plu_codes.commodity', 'asc') // Group by commodity first
            ->orderBy('list_items.organic', 'asc') // Within commodity: regular first, then organic
            ->orderByRaw('CAST(plu_codes.plu AS UNSIGNED) ASC') // Within organic status: PLU code ascending
            ->select('list_items.*')
            ->get()
            ->load('pluCode'); // Ensure pluCode relationship is loaded

        // For display, only show items with inventory > 0
        $this->listItems = $this->allListItems->filter(function ($item) {
            return $item->inventory_level > 0;
        });
    }

    // Prepare carousel - read-only version
    public function openCarousel()
    {
        $this->dispatch('carousel-ready-to-open');
    }

    public function toggleCopyModal()
    {
        $this->showCopyModal = ! $this->showCopyModal;

        if ($this->showCopyModal) {
            $this->customListName = $this->userList->name;
        } else {
            $this->resetCopyForm();
        }
    }

    public function copyList()
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        // Validate the custom name
        if (empty(trim($this->customListName))) {
            session()->flash('error', 'Please provide a name for your list.');

            return;
        }

        try {
            // Copy the list for the current user WITH inventory levels
            // Use the UserList method which copies ALL items (including those with 0 inventory)
            $newList = $this->userList->copyForUserWithInventory(
                Auth::user(),
                trim($this->customListName)
            );

            // Close modal and redirect to the new list
            $this->showCopyModal = false;
            session()->flash('message', 'List copied successfully to your lists with inventory levels preserved!');

            return redirect()->route('lists.show', $newList);

        } catch (\Exception $e) {
            session()->flash('error', 'Failed to copy list. Please try again.');
        }
    }

    protected function resetCopyForm()
    {
        $this->customListName = '';
    }

    public function render()
    {
        // Create a map of PLU codes that have both regular and organic versions
        $dualVersionPluCodes = $this->listItems->groupBy('plu_code_id')
            ->filter(function ($items) {
                return $items->where('organic', true)->isNotEmpty() &&
                       $items->where('organic', false)->isNotEmpty();
            })
            ->keys();

        return view('livewire.lists.shared-view', [
            'listItems' => $this->listItems,
            'dualVersionPluCodes' => $dualVersionPluCodes,
        ])->layout('layouts.app');
    }
}
