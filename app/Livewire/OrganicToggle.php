<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\ListItem;

class OrganicToggle extends Component
{
    public $listItemId;
    public $isOrganic;

    public function mount(ListItem $listItem)
    {
        $this->listItemId = $listItem->id;
        $this->isOrganic = $listItem->organic;
    }

    public function toggleOrganic()
    {
        $listItem = ListItem::find($this->listItemId);
        $listItem->update(['organic' => !$listItem->organic]);
        $this->isOrganic = $listItem->organic;

        $this->dispatch('list-item-updated');
    }

    public function render()
    {
        return view('livewire.organic-toggle');
    }
}
