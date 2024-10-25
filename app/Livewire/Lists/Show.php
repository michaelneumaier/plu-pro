<?php

namespace App\Livewire\Lists;

use Livewire\Component;
use App\Models\UserList;
use App\Models\PLUCode;
use App\Models\ListItem;

class Show extends Component
{
    public $userId;
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

        //$searchTerm = '%' . addcslashes(trim($this->searchTerm), '%_') . '%';

        $this->availablePLUCodes = PLUCode::where('plu', 'like', '%' . $this->searchTerm . '%')
            ->orWhere('variety', 'like', '%' . $this->searchTerm . '%')
            ->orWhere('commodity', 'like', '%' . $this->searchTerm . '%')
            ->orWhere('aka', 'like', '%' . $this->searchTerm . '%')
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

    public function removePLUCode($pluCodeId)
    {
        // Find the ListItem corresponding to the given PLU Code ID
        $listItem = $this->userList->listItems()->where('plu_code_id', $pluCodeId)->first();

        // Check if the ListItem exists
        if ($listItem) {
            $listItem->delete(); // Delete the ListItem
            session()->flash('message', 'PLU Code removed from your list.'); // Optional: Flash message for success
        } else {
            session()->flash('error', 'PLU Code not found in your list.'); // Optional: Flash message for error
        }

        $this->dispatch('refreshComponent'); // Refresh the component
    }

    public function deletePlu($pluCodeId)
    {
        // Find the ListItem corresponding to this PLU Code for the user
        $listItem = ListItem::where('user_id', $this->userId)
            ->where('plu_id', $pluCodeId)
            ->first();

        if ($listItem) {
            $listItem->delete();
            session()->flash('message', 'PLU Code removed from your list.');
        } else {
            session()->flash('error', 'PLU Code not found in your list.');
        }
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
