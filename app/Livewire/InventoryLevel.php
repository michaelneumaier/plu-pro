<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\ListItem;

class InventoryLevel extends Component
{
    public $listItemId;
    public $userListId;
    public bool $isEditing = false;
    public string $editableValue = '';
    public $inventoryLevel;

    protected $listeners = [
        'inventoryUpdated' => '$refresh',
        'filter-changed' => '$refresh'
    ];

    public function boot()
    {
        $this->refreshValue();
    }

    public function mount($listItemId, $userListId)
    {
        $this->listItemId = $listItemId;
        $this->userListId = $userListId;
        $this->refreshValue();
    }

    protected function getListItem()
    {
        return ListItem::where('id', $this->listItemId)
            ->where('user_list_id', $this->userListId)
            ->first();
    }

    protected function refreshValue()
    {
        $listItem = $this->getListItem();
        if ($listItem) {
            $this->inventoryLevel = $listItem->inventory_level;
            $this->editableValue = number_format($listItem->inventory_level, 1);
        }
    }

    public function startEditing()
    {
        $this->isEditing = true;
        $this->refreshValue();
    }

    public function saveEdit()
    {
        $this->validate();

        $listItem = $this->getListItem();
        if ($listItem) {
            $newValue = (float) $this->editableValue;
            if ($newValue !== $listItem->inventory_level) {
                $listItem->update(['inventory_level' => $newValue]);
                $this->dispatch('inventoryUpdated');
            }
        }

        $this->isEditing = false;
        $this->resetValidation();
    }

    public function increment()
    {
        $listItem = $this->getListItem();
        if ($listItem) {
            $listItem->increment('inventory_level', 1);
            $this->dispatch('inventoryUpdated');
        }
    }

    public function decrement()
    {
        $listItem = $this->getListItem();
        if ($listItem && $listItem->inventory_level >= 1) {
            $listItem->decrement('inventory_level', 1);
            $this->dispatch('inventoryUpdated');
        }
    }

    public function addHalf()
    {
        $listItem = $this->getListItem();
        if ($listItem) {
            $listItem->increment('inventory_level', 0.5);
            $this->dispatch('inventoryUpdated');
        }
    }

    public function subtractHalf()
    {
        $listItem = $this->getListItem();
        if ($listItem && ($listItem->inventory_level - 0.5) >= 0) {
            $listItem->decrement('inventory_level', 0.5);
            $this->dispatch('inventoryUpdated');
        }
    }

    public function render()
    {
        $listItem = $this->getListItem();

        if (!$listItem) {
            return null;
        }

        return view('livewire.inventory-level', [
            'listItem' => $listItem,
            'currentValue' => $this->inventoryLevel
        ]);
    }
}
