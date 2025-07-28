<?php

namespace App\Livewire\Marketplace;

use App\Models\UserList;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ViewList extends Component
{
    public UserList $marketplaceList;

    public Collection $listItems;

    public string $customListName = '';

    public bool $showCopyModal = false;

    public function mount($shareCode)
    {
        // Find the marketplace list by share code
        $this->marketplaceList = UserList::marketplace()
            ->where('share_code', $shareCode)
            ->firstOrFail();

        $this->loadListItems();
        $this->customListName = $this->marketplaceList->marketplace_title;

        // Increment view count
        $this->marketplaceList->incrementViewCount();
    }

    protected function loadListItems()
    {
        // Get all list items sorted the same way as the original list
        $this->listItems = $this->marketplaceList->listItems()
            ->with(['pluCode'])
            ->join('plu_codes', 'list_items.plu_code_id', '=', 'plu_codes.id')
            ->orderBy('plu_codes.commodity', 'asc')
            ->orderBy('list_items.organic', 'asc')
            ->orderByRaw('CAST(plu_codes.plu AS UNSIGNED) ASC')
            ->select('list_items.*')
            ->get()
            ->load('pluCode');
    }

    public function toggleCopyModal()
    {
        $this->showCopyModal = ! $this->showCopyModal;

        if ($this->showCopyModal) {
            $this->customListName = $this->marketplaceList->marketplace_title;
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
            // Copy the list for the current user
            $newList = $this->marketplaceList->copyForUser(
                Auth::user(),
                trim($this->customListName)
            );

            // Close modal and redirect to the new list
            $this->showCopyModal = false;
            session()->flash('message', 'List copied successfully to your lists!');

            return redirect()->route('lists.show', $newList);

        } catch (\Exception $e) {
            session()->flash('error', 'Failed to copy list. Please try again.');
        }
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

        return view('livewire.marketplace.view-list', [
            'listItems' => $this->listItems,
            'dualVersionPluCodes' => $dualVersionPluCodes,
            'categories' => UserList::getMarketplaceCategories(),
        ])->layout('layouts.app');
    }
}
