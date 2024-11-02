<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\ListItem;

class InventoryLevel extends Component
{
    public ListItem $listItem;
    public bool $isEditing = false;
    public string $editableValue = '';

    protected $listeners = ['inventoryUpdated' => '$refresh'];

    protected $rules = [
        'editableValue' => ['required', 'numeric', 'min:0', 'regex:/^\d*\.?[05]$/'],
    ];

    protected $messages = [
        'editableValue.regex' => 'Value must be in .5 increments.',
    ];

    public function startEditing()
    {
        $this->isEditing = true;
        $this->editableValue = number_format($this->listItem->inventory_level, 1);
    }

    public function saveEdit()
    {
        $this->validate();

        $newValue = (float) $this->editableValue;
        if ($newValue !== $this->listItem->inventory_level) {
            $this->listItem->update(['inventory_level' => $newValue]);
            $this->listItem->refresh();
            $this->dispatch('inventoryUpdated');
        }

        $this->isEditing = false;
        $this->resetValidation();
    }

    public function increment()
    {
        $this->listItem->increment('inventory_level', 1);
        $this->listItem->refresh();
        $this->dispatch('inventoryUpdated');
    }

    public function decrement()
    {
        if ($this->listItem->inventory_level >= 1) {
            $this->listItem->decrement('inventory_level', 1);
            $this->listItem->refresh();
            $this->dispatch('inventoryUpdated');
        }
    }

    public function addHalf()
    {
        $this->listItem->increment('inventory_level', 0.5);
        $this->listItem->refresh();
        $this->dispatch('inventoryUpdated');
    }

    public function subtractHalf()
    {
        if (($this->listItem->inventory_level - 0.5) >= 0) {
            $this->listItem->decrement('inventory_level', 0.5);
            $this->listItem->refresh();
            $this->dispatch('inventoryUpdated');
        }
    }

    public function render()
    {
        return view('livewire.inventory-level');
    }
}
