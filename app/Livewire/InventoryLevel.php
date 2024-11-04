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
        $this->validate([
            'editableValue' => ['required', 'numeric', 'min:0', 'regex:/^\d*\.?[05]$/'],
        ], [
            'editableValue.regex' => 'Value must be in .5 increments.',
        ]);

        $newValue = (float) $this->editableValue;
        $oldValue = $this->inventoryLevel;
        $this->inventoryLevel = $newValue;

        $listItem = $this->getListItem();
        if ($listItem) {
            try {
                $listItem->update(['inventory_level' => $newValue]);
            } catch (\Exception $e) {
                $this->inventoryLevel = $oldValue;
                $this->dispatch('notify', [
                    'type' => 'error',
                    'message' => 'Failed to update inventory. Please try again.'
                ]);
            }
        }

        $this->isEditing = false;
        $this->resetValidation();
    }

    public function increment()
    {
        $this->inventoryLevel += 1;

        $listItem = $this->getListItem();
        if ($listItem) {
            try {
                $listItem->increment('inventory_level', 1);
            } catch (\Exception $e) {
                $this->inventoryLevel -= 1;
                $this->dispatch('notify', [
                    'type' => 'error',
                    'message' => 'Failed to update inventory. Please try again.'
                ]);
            }
        }
    }

    public function decrement()
    {
        if ($this->inventoryLevel >= 1) {
            $this->inventoryLevel -= 1;

            $listItem = $this->getListItem();
            if ($listItem) {
                try {
                    $listItem->decrement('inventory_level', 1);
                } catch (\Exception $e) {
                    $this->inventoryLevel += 1;
                    $this->dispatch('notify', [
                        'type' => 'error',
                        'message' => 'Failed to update inventory. Please try again.'
                    ]);
                }
            }
        }
    }

    public function addHalf()
    {
        $this->inventoryLevel += 0.5;

        $listItem = $this->getListItem();
        if ($listItem) {
            try {
                $listItem->increment('inventory_level', 0.5);
            } catch (\Exception $e) {
                $this->inventoryLevel -= 0.5;
                $this->dispatch('notify', [
                    'type' => 'error',
                    'message' => 'Failed to update inventory. Please try again.'
                ]);
            }
        }
    }

    public function subtractHalf()
    {
        if (($this->inventoryLevel - 0.5) >= 0) {
            $this->inventoryLevel -= 0.5;

            $listItem = $this->getListItem();
            if ($listItem) {
                try {
                    $listItem->decrement('inventory_level', 0.5);
                } catch (\Exception $e) {
                    $this->inventoryLevel += 0.5;
                    $this->dispatch('notify', [
                        'type' => 'error',
                        'message' => 'Failed to update inventory. Please try again.'
                    ]);
                }
            }
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
