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

    protected $listeners = [
        'open-carousel' => 'open',
        'close-carousel' => 'close',
    ];

    public function mount(int $userListId)
    {
        $this->userListId = $userListId;
        $this->items = collect(); // Initialize as empty collection
        $this->fetchItems();
    }

    public function fetchItems()
    {
        $userList = UserList::with(['listItems.pluCode'])->find($this->userListId);

        if ($userList) {
            // Filter out items with zero or null inventory_level
            $this->items = $userList->listItems->filter(function ($item) {
                return $item->inventory_level > 0;
            });
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
        $this->isOpen = true;
        $this->fetchItems();
        $this->currentIndex = 0;
    }

    public function close()
    {
        $this->isOpen = false;
    }

    public function previous()
    {
        if ($this->currentIndex > 0) {
            $this->currentIndex--;
        } else {
            // Optionally loop to the last item
            $this->currentIndex = max(0, $this->items->count() - 1);
        }
    }

    public function next()
    {
        if ($this->currentIndex < ($this->items->count() - 1)) {
            $this->currentIndex++;
        } else {
            // Optionally loop to the first item
            $this->currentIndex = 0;
        }
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
