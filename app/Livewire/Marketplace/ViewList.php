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
        // Get all list items (both PLU and UPC) sorted properly
        $allItems = $this->marketplaceList->listItems()
            ->with(['pluCode', 'upcCode'])
            ->get();

        // Sort all items with a single combined sort key: commodity + sort_priority + code
        // Order: Regular PLU, Organic PLU, UPC
        $this->listItems = $allItems->sortBy(function ($item) {
            if ($item->item_type === 'plu' && $item->pluCode) {
                $commodity = $item->pluCode->commodity;
                $code = str_pad($item->pluCode->plu, 10, '0', STR_PAD_LEFT);
            } elseif ($item->item_type === 'upc' && $item->upcCode) {
                $commodity = $item->upcCode->commodity;
                $code = $item->upcCode->name;
            } else {
                return 'ZZZ|9|ZZZ'; // Put items without codes at the end
            }

            // Priority: Regular PLU (0), Organic PLU (1), UPC (2)
            if ($item->item_type === 'plu' && ! $item->organic) {
                $priority = '0'; // Regular PLU first
            } elseif ($item->item_type === 'plu' && $item->organic) {
                $priority = '1'; // Organic PLU second
            } else {
                $priority = '2'; // UPC last
            }

            return $commodity.'|'.$priority.'|'.$code;
        });
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
        // Create a map of PLU codes that have both regular and organic versions (only for PLU items)
        $dualVersionPluCodes = $this->listItems->where('item_type', 'plu')
            ->whereNotNull('plu_code_id')
            ->groupBy('plu_code_id')
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
