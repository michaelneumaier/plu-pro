<?php

namespace App\Livewire;

use App\Models\UserList;
use Illuminate\Support\Collection;
use Livewire\Attributes\On;
use Livewire\Component;

class ItemCarousel extends Component
{
    public int $userListId;

    public Collection $items;

    public int $currentIndex = 0;

    public bool $isOpen = false;

    public bool $isLoading = false;

    protected $listeners = [
        'open-carousel' => 'open',
        'close-carousel' => 'close',
        'list-items-updated' => 'refreshItems',
    ];

    public function mount(int $userListId)
    {
        $this->userListId = $userListId;
        $this->items = collect(); // Initialize as empty collection
        $this->fetchItems();
    }

    public function fetchItems()
    {
        $userList = UserList::find($this->userListId);

        if ($userList) {
            // Get items with inventory, both PLU and UPC items
            $this->items = $userList->listItems()
                ->with(['pluCode', 'upcCode'])
                ->where('list_items.inventory_level', '>', 0) // Only items with inventory
                ->get()
                ->map(function ($item) {
                    // Add computed display properties for unified handling
                    if ($item->item_type === 'plu' && $item->pluCode) {
                        $item->display_code = $item->organic ? '9'.$item->pluCode->plu : $item->pluCode->plu;
                        $item->display_name = $item->pluCode->variety;
                        $item->display_commodity = $item->pluCode->commodity;
                        $item->display_category = $item->pluCode->category;
                    } elseif ($item->item_type === 'upc' && $item->upcCode) {
                        $item->display_code = $item->upcCode->upc;
                        $item->display_name = $item->upcCode->name;
                        $item->display_commodity = $item->upcCode->commodity;
                        $item->display_category = $item->upcCode->category ?? 'UPC';
                    } else {
                        // Fallback for invalid items
                        $item->display_code = 'N/A';
                        $item->display_name = 'Unknown Item';
                        $item->display_commodity = 'UNKNOWN';
                        $item->display_category = 'Unknown';
                    }

                    return $item;
                })
                ->sortBy([
                    ['display_commodity', 'asc'],     // Group by commodity first
                    ['item_type', 'asc'],             // PLU first, then UPC
                    ['organic', 'asc'],               // Regular before organic for PLU
                    ['display_code', 'asc'],           // Then by code/name
                ])
                ->values(); // Re-index the collection
        } else {
            $this->items = collect();
        }

        // Reset currentIndex if it exceeds the new items count
        if ($this->currentIndex >= $this->items->count()) {
            $this->currentIndex = max(0, $this->items->count() - 1);
        }
    }

    #[On('carousel-open')]
    public function openCarousel()
    {
        $this->isLoading = true;
        $this->isOpen = true;
        // Always fetch fresh items when opening
        $this->fetchItems();
        $this->currentIndex = 0;
        $this->isLoading = false;
        // Force a complete re-render by updating the component key
        $this->dispatch('carousel-items-updated');
    }

    public function close()
    {
        $this->isOpen = false;
    }

    public function refreshItems()
    {
        // Only refresh if carousel is currently open
        if ($this->isOpen) {
            $this->fetchItems();
        }
    }

    public function previous()
    {
        if ($this->currentIndex > 0) {
            $this->currentIndex--;
        }
        // Stop at first item - no looping
    }

    public function next()
    {
        if ($this->currentIndex < ($this->items->count() - 1)) {
            $this->currentIndex++;
        }
        // Stop at last item - no looping
    }

    public function getCurrentItemProperty()
    {
        if ($this->items->isEmpty()) {
            return null;
        }

        return $this->items->values()->get($this->currentIndex);
    }

    public function render()
    {
        return view('livewire.item-carousel');
    }
}
