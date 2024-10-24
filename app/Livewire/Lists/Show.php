<?php

namespace App\Livewire\Lists;

use Livewire\Component;
use App\Models\UserList;
use App\Models\PLUCode;
use App\Models\ListItem;

class Show extends Component
{
    public $userList;
    public $searchTerm;
    public $availablePLUCodes;

    protected $listeners = ['refreshComponent' => '$refresh'];

    public function mount(UserList $userList)
    {
        $this->userList = $userList;
        $this->availablePLUCodes = collect();
    }

    public function updatedSearchTerm()
    {
        if (strlen(trim($this->searchTerm)) < 2) {
            $this->availablePLUCodes = collect(); // Empty collection
            return;
        }

        $searchTerm = '%' . addcslashes(trim($this->searchTerm), '%_') . '%';

        $this->availablePLUCodes = PLUCode::whereRaw(
            'plu LIKE ? OR variety LIKE ? OR commodity LIKE ? OR aka LIKE ?',
            [$searchTerm, $searchTerm, $searchTerm, $searchTerm]
        )
            ->limit(30)
            ->get();
    }

    public function addPLUCode($pluCodeId)
    {
        $exists = $this->userList->listItems()->where('plu_code_id', $pluCodeId)->exists();
        if (!$exists) {
            $this->userList->listItems()->create([
                'plu_code_id' => $pluCodeId,
            ]);
        }

        $this->dispatch('refreshComponent');
    }

    public function removePLUCode($listItemId)
    {
        $listItem = ListItem::findOrFail($listItemId);
        $listItem->delete();

        $this->dispatch('refreshComponent');
    }

    public function render()
    {
        $listItems = $this->userList->listItems()->with('pluCode')->get();

        return view('livewire.lists.show', [
            'listItems' => $listItems,
            'availablePLUCodes' => $this->availablePLUCodes,
        ]);
    }
}
