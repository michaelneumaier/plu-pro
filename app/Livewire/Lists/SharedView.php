<?php

namespace App\Livewire\Lists;

use Livewire\Component;
use App\Models\UserList;
use Illuminate\Support\Collection;

class SharedView extends Component
{
    public UserList $userList;
    public Collection $listItems;

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
        // Get all list items with same sorting as the owner's view
        $this->listItems = $this->userList->listItems()
            ->with(['pluCode'])
            ->join('plu_codes', 'list_items.plu_code_id', '=', 'plu_codes.id')
            ->orderBy('plu_codes.commodity', 'asc') // Group by commodity first
            ->orderBy('list_items.organic', 'asc') // Within commodity: regular first, then organic
            ->orderByRaw('CAST(plu_codes.plu AS UNSIGNED) ASC') // Within organic status: PLU code ascending
            ->select('list_items.*')
            ->get()
            ->load('pluCode'); // Ensure pluCode relationship is loaded
    }

    // Prepare carousel - read-only version
    public function openCarousel()
    {
        $this->dispatch('carousel-ready-to-open');
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
