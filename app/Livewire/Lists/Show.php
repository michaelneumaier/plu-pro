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

    public $selectedCommodity = '';
    public $selectedCategory = '';

    // Available filter options
    public $commodities = [];
    public $categories = [];

    protected $listeners = [
        'refreshComponent' => '$refresh',
        'filtersUpdated' => 'handleFiltersUpdated',
    ];

    protected $queryString = [
        'searchTerm' => ['except' => ''],
        'selectedCommodity' => ['except' => ''],
        'selectedCategory' => ['except' => ''],
    ];

    public function mount(UserList $userList)
    {
        $this->userList = $userList;
        $this->availablePLUCodes = collect();
    }

    protected function initializeFilterOptions()
    {

        $userList = $this->userList->listItems()->with('pluCode')->get();
        $this->commodities =
            array_column($this->processCollection($userList)->select('commodity')->unique('commodity')->sortBy('commodity')->toArray(), 'commodity');
        $this->categories =
            array_column($this->processCollection($userList)->select('category')->unique('category')->sortBy('category')->toArray(), 'category');
    }

    public function handleFiltersUpdated($filters)
    {
        $this->selectedCategory = $filters['selectedCategory'];
        $this->selectedCommodity = $filters['selectedCommodity'];
        $this->dispatch('refreshComponent');
    }

    public function updatedSelectedCategory()
    {
        $this->resetPage();
    }

    protected function processCollection($collection)
    {
        // Check if the first item is a PLUCode instance
        if ($collection->first() instanceof PLUCode) {
            return $collection;
        }

        // Otherwise, assume it's a collection of List Items with 'plu_id'
        // Extract PLU IDs
        $pluIds = $collection->pluck('plu_code_id')->unique();

        // Fetch PLU Codes based on IDs
        return PLUCode::whereIn('id', $pluIds)->get();
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
        $this->initializeFilterOptions();
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

        $this->initializeFilterOptions();
        $this->dispatch('refreshComponent');
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
        // Start with the list items associated with the user list
        $listItemsQuery = $this->userList->listItems();

        // Apply the commodity filter if selected
        if ($this->selectedCommodity) {
            $listItemsQuery->whereHas('pluCode', function ($query) {
                $query->where('commodity', $this->selectedCommodity);
            });
        }

        if ($this->selectedCategory) {
            $listItemsQuery->whereHas('pluCode', function ($query) {
                $query->where('category', $this->selectedCategory);
            });
        }

        // Eager load the pluCode relationship and retrieve the filtered list items
        $listItems = $listItemsQuery->with('pluCode')->get();

        return view('livewire.lists.show', [
            'listItems' => $listItems,
            'availablePLUCodes' => $this->availablePLUCodes,
        ]);
    }
}
