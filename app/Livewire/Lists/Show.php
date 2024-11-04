<?php

namespace App\Livewire\Lists;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Url;
use App\Models\UserList;
use App\Models\PLUCode;
use App\Models\ListItem;

class Show extends Component
{
    use WithPagination;

    public $userId;
    public $userList;
    public $searchTerm;
    //public $availablePLUCodes;

    #[Url]
    public $selectedCategory = '';

    #[Url]
    public $selectedCommodity = '';

    // Available filter options
    public $commodities = [];
    public $categories = [];

    protected $listeners = [
        'filtersUpdated' => 'handleFiltersUpdated',
    ];

    protected $queryString = [
        'searchTerm' => ['except' => ''],
        'selectedCommodity' => ['except' => ''],
        'selectedCategory' => ['except' => ''],
        'page' => ['except' => 1],
    ];

    // Add this property to specify which page parameter to use for available PLU codes
    protected $paginationTheme = 'tailwind';

    #[Url]
    public $page = 1;

    public function mount(UserList $userList)
    {
        $this->userList = $userList;
        //$this->availablePLUCodes = collect();
        $this->initializeFilterOptions();
    }

    protected function updatedUserList()
    {
        $this->initializeFilterOptions();
    }

    protected function initializeFilterOptions()
    {
        $userList = $this->userList->listItems()->with('pluCode')->get();

        // Update commodities
        $this->commodities = $this->processCollection($userList)
            ->pluck('commodity')
            ->unique()
            ->sort()
            ->values()
            ->toArray();

        // Update categories
        $this->categories = $this->processCollection($userList)
            ->pluck('category')
            ->unique()
            ->sort()
            ->values()
            ->toArray();
        $this->dispatch('refreshFilters', commodities: $this->commodities, categories: $this->categories);
    }

    public function handleFiltersUpdated($filters)
    {
        $this->selectedCategory = $filters['selectedCategory'];
        $this->selectedCommodity = $filters['selectedCommodity'];

        // Force a re-render of the entire component
        $this->render();
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

        // Otherwise, assume it's a collection of List Items with 'plu_code_id'
        // Extract PLU IDs
        $pluIds = $collection->pluck('plu_code_id')->unique();

        // Fetch PLU Codes based on IDs with ListItems loaded
        return PLUCode::whereIn('id', $pluIds)->with(['listItems' => function ($query) {
            $query->where('user_list_id', $this->userList->id);
        }])->get();
    }

    public function updatedSearchTerm()
    {
        $this->page = 1;
    }

    public function addPLUCode($pluCodeId)
    {
        $exists = $this->userList->listItems()->where('plu_code_id', $pluCodeId)->exists();
        if (!$exists) {
            $this->userList->listItems()->create([
                'plu_code_id' => $pluCodeId,
                'inventory_level' => 0.0,
            ]);
        }
        $this->initializeFilterOptions();
        // Force a complete refresh of the component
        $this->dispatch('refresh-list')->self();
    }

    public function removePLUCode($pluCodeId)
    {
        $listItem = $this->userList->listItems()->where('plu_code_id', $pluCodeId)->first();

        if ($listItem) {
            $listItem->delete();
            session()->flash('message', 'PLU Code removed from your list.');
        } else {
            session()->flash('error', 'PLU Code not found in your list.');
        }

        $this->initializeFilterOptions();
    }

    public function deletePlu($pluCodeId)
    {
        // Find the ListItem corresponding to this PLU Code for the user
        $listItem = ListItem::where('user_list_id', $this->userList->id) // Corrected field
            ->where('plu_code_id', $pluCodeId)
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
        $availablePLUCodesQuery = PLUCode::query();
        $pluCodes = collect();

        if (strlen(trim($this->searchTerm)) >= 2) {
            $availablePLUCodesQuery->where(function ($q) {
                $q->where('plu', 'like', '%' . $this->searchTerm . '%')
                    ->orWhere('variety', 'like', '%' . $this->searchTerm . '%')
                    ->orWhere('commodity', 'like', '%' . $this->searchTerm . '%')
                    ->orWhere('aka', 'like', '%' . $this->searchTerm . '%');
            });
            $pluCodes = $availablePLUCodesQuery->paginate(10)->withQueryString();
        }

        // Get the list items query
        $listItemsQuery = $this->userList->listItems()
            ->with(['pluCode']);

        // Apply filters
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

        // Get paginated results for available PLU codes

        return view('livewire.lists.show', [
            'listItems' => $listItemsQuery->get(),
            'pluCodes' => $pluCodes,
            'categories' => $this->categories,
            'commodities' => $this->commodities,
        ]);
    }
}
