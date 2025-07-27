<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\On;
use App\Models\ListItem;
use App\Models\UserList;
use Illuminate\Support\Collection;

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
            // Get items with same sorting as main list: commodity -> organic status -> PLU code
            $this->items = $userList->listItems()
                ->with(['pluCode'])
                ->join('plu_codes', 'list_items.plu_code_id', '=', 'plu_codes.id')
                ->where('list_items.inventory_level', '>', 0) // Only items with inventory
                ->orderBy('plu_codes.commodity', 'asc') // Group by commodity first
                ->orderBy('list_items.organic', 'asc') // Within commodity: regular first, then organic
                ->orderByRaw('CAST(plu_codes.plu AS UNSIGNED) ASC') // Within organic status: PLU code ascending
                ->select('list_items.*')
                ->get()
                ->load('pluCode'); // Ensure pluCode relationship is loaded
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
