<?php

namespace App\Livewire\Lists;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Url;
use App\Models\UserList;
use App\Models\PLUCode;
use App\Models\ListItem;
use Illuminate\Support\Facades\DB;

class Show extends Component
{
    use WithPagination;

    public $userId;
    public $userList;
    public $searchTerm;
    //public $availablePLUCodes;

    // Remove URL-based filtering since we're doing it client-side
    // public $selectedCategory = '';
    // public $selectedCommodity = '';

    // Available filter options
    public $commodities = [];
    public $categories = [];

    protected $listeners = [
        'retry-add-plu' => 'retryAddPlu',
    ];

    protected $queryString = [
        'searchTerm' => ['except' => ''],
        'page' => ['except' => 1],
    ];

    // Add this property to specify which page parameter to use for available PLU codes
    protected $paginationTheme = 'tailwind';

    #[Url]
    public $page = 1;

    public $isProcessing = false;

    public function mount(UserList $userList)
    {
        $this->userList = $userList;
        
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
            ->filter() // Remove null/empty values
            ->unique()
            ->sort()
            ->values()
            ->toArray();

        // Update categories
        $this->categories = $this->processCollection($userList)
            ->pluck('category')
            ->filter() // Remove null/empty values
            ->unique()
            ->sort()
            ->values()
            ->toArray();

        $this->dispatch('refreshFilters', commodities: $this->commodities, categories: $this->categories);
    }

    // Filter methods removed - using client-side filtering now

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
        $this->resetPage();
    }

    public function addPLUCode($pluCodeId, $organic = false)
    {
        $exists = $this->userList->listItems()
            ->where('plu_code_id', $pluCodeId)
            ->exists();

        if (!$exists) {
            DB::transaction(function () use ($pluCodeId, $organic) {
                $listItem = $this->userList->listItems()->create([
                    'plu_code_id' => $pluCodeId,
                    'inventory_level' => 0.0,
                    'organic' => $organic,
                ]);

                // Force a refresh of the relationships
                $this->userList->load(['listItems.pluCode']);
                $this->initializeFilterOptions();
                
                // Notify any listening carousel components that items have changed
                $this->dispatch('list-items-updated');
            });
            
            // For now, force a page refresh to ensure everything works properly
            session()->flash('message', 'Item added successfully!');
            $this->dispatch('item-added-refresh');
        }
    }

    public function retryAddPlu($pluCodeId)
    {
        $this->addPLUCode($pluCodeId);
    }

    public function removePLUCode($pluCodeId)
    {
        DB::transaction(function () use ($pluCodeId) {
            $listItem = $this->userList->listItems()
                ->where('plu_code_id', $pluCodeId)
                ->first();

            if ($listItem) {
                $listItem->delete();
                $this->userList->load(['listItems.pluCode']);
                $this->initializeFilterOptions();
                session()->flash('message', 'PLU Code removed from your list.');
            } else {
                session()->flash('error', 'PLU Code not found in your list.');
            }
        });
        
        // Simple approach - let Livewire naturally re-render
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

    public function clearAllInventoryLevels()
    {
        DB::transaction(function () {
            $updated = $this->userList->listItems()
                ->update(['inventory_level' => 0.0]);

            if ($updated > 0) {
                session()->flash('message', "Successfully reset {$updated} items to 0 inventory.");
            } else {
                session()->flash('message', 'No items found to update.');
            }

            // Refresh the list to show updated values
            $this->userList->load(['listItems.pluCode']);
        });

        // Dispatch JavaScript event to clear Alpine.js local state and refresh page
        $this->dispatch('inventory-cleared-refresh');
    }

    public function prepareAndOpenCarousel()
    {
        // Dispatch event to force all inventory components to sync
        $this->dispatch('force-inventory-sync');
        
        // Add a delay to allow syncs to complete
        sleep(1);
        
        // Force a refresh of all list items to get latest values from database
        $this->userList->load(['listItems.pluCode']);
        
        // Dispatch event to open carousel with fresh data
        $this->dispatch('carousel-ready-to-open');
    }

    public function render()
    {
        $query = PLUCode::query();
        $searchResults = collect();

        if (strlen(trim($this->searchTerm)) >= 2) {
            $query->where(function ($q) {
                $q->where('plu', 'like', '%' . $this->searchTerm . '%')
                    ->orWhere('variety', 'like', '%' . $this->searchTerm . '%')
                    ->orWhere('commodity', 'like', '%' . $this->searchTerm . '%')
                    ->orWhere('aka', 'like', '%' . $this->searchTerm . '%');
            });
            $searchResults = $query->paginate(10)->withQueryString();
        }

        // Get all list items for client-side filtering
        $listItems = $this->userList->listItems()
            ->with(['pluCode'])
            ->get();

        // Prepare items data for JavaScript
        $allItemsData = $listItems->map(function($item) {
            return [
                'id' => $item->id,
                'plu_code_id' => $item->plu_code_id,
                'plu' => $item->pluCode->plu,
                'variety' => $item->pluCode->variety,
                'commodity' => $item->pluCode->commodity,
                'category' => $item->pluCode->category,
                'organic' => $item->organic,
                'inventory_level' => $item->inventory_level,
                'size' => $item->pluCode->size,
                'retail_price' => $item->pluCode->retail_price,
                'consumer_usage_tier' => $item->pluCode->consumer_usage_tier
            ];
        });

        return view('livewire.lists.show', [
            'listItems' => $listItems,
            'pluCodes' => $searchResults,
            'categories' => $this->categories,
            'commodities' => $this->commodities,
            'allItemsData' => $allItemsData,
        ]);
    }
}
