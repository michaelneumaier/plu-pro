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

    // Server-side filtering to match server-side sorting
    public $selectedCategory = '';
    public $selectedCommodity = '';

    // Available filter options
    public $commodities = [];
    public $categories = [];
    
    // Refresh token to prevent snapshot missing errors
    public $refreshToken;

    protected $listeners = [
        'retry-add-plu' => 'retryAddPlu',
    ];

    protected $queryString = [
        'searchTerm' => ['except' => ''],
        'selectedCategory' => ['except' => ''],
        'selectedCommodity' => ['except' => ''],
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
        $this->refreshToken = time(); // Initialize refresh token
        
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
    
    public function updatedSelectedCategory()
    {
        $this->resetPage();
    }
    
    public function updatedSelectedCommodity()
    {
        $this->resetPage();
    }
    
    public function resetFilters()
    {
        $this->selectedCategory = '';
        $this->selectedCommodity = '';
        $this->resetPage();
    }

    public function addPLUCodeSilent($pluCodeId, $organic = false)
    {
        // This method adds items without triggering component re-render
        // Check if this specific version (regular or organic) already exists
        $exists = $this->userList->listItems()
            ->where('plu_code_id', $pluCodeId)
            ->where('organic', $organic)
            ->exists();

        if (!$exists) {
            $listItem = DB::transaction(function () use ($pluCodeId, $organic) {
                return $this->userList->listItems()->create([
                    'plu_code_id' => $pluCodeId,
                    'inventory_level' => 0.0,
                    'organic' => $organic,
                ]);
            });

            // Load the PLU code data for the new item
            $listItem->load('pluCode');
            
            // Update refresh token to reset wire:key values and prevent snapshot errors
            $this->refreshToken = time();
            
            // Dispatch event to manually append the new item to DOM
            $this->dispatch('manually-append-item', [
                'listItem' => $listItem->toArray(),
                'pluCode' => $listItem->pluCode->toArray()
            ]);
            
            // Return success without modifying component properties
            return ['success' => true, 'listItem' => $listItem];
        }
        
        return ['success' => false, 'message' => 'This ' . ($organic ? 'organic' : 'regular') . ' item already exists in your list'];
    }

    public function addPLUCode($pluCodeId, $organic = false)
    {
        // Use the silent method to avoid re-render issues
        $result = $this->addPLUCodeSilent($pluCodeId, $organic);
        
        if ($result['success']) {
            session()->flash('message', 'Item added successfully!');
            
            // Update the relationships for subsequent renders
            $this->userList->load(['listItems.pluCode']);
            $this->initializeFilterOptions();
            
            // Notify any listening carousel components that items have changed
            $this->dispatch('list-items-updated');
        }
    }


    public function retryAddPlu($pluCodeId)
    {
        $this->addPLUCode($pluCodeId);
    }


    public function removeListItem($listItemId)
    {
        DB::transaction(function () use ($listItemId) {
            $listItem = $this->userList->listItems()
                ->where('id', $listItemId)
                ->first();

            if ($listItem) {
                $listItem->delete();
                $this->userList->load(['listItems.pluCode']);
                $this->initializeFilterOptions();
                
                // Update refresh token to force component refresh
                $this->refreshToken = time();
                
                session()->flash('message', 'Item removed from your list.');
            } else {
                session()->flash('error', 'Item not found in your list.');
            }
        });
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

    // Headless version for removing items
    public function removePLUCodeHeadless($pluCodeId)
    {
        try {
            $listItem = $this->userList->listItems()
                ->where('plu_code_id', $pluCodeId)
                ->first();

            if ($listItem) {
                $listItem->delete();
                return ['success' => true];
            } else {
                return ['success' => false, 'message' => 'Item not found'];
            }
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Failed to remove item'];
        }
    }

    // Headless version for updating list items
    public function updateListItemHeadless($listItemId, $updates)
    {
        try {
            $listItem = $this->userList->listItems()
                ->where('id', $listItemId)
                ->first();

            if ($listItem) {
                // Only allow specific fields to be updated
                $allowedFields = ['organic', 'inventory_level'];
                $filteredUpdates = array_intersect_key($updates, array_flip($allowedFields));
                
                $listItem->update($filteredUpdates);
                return ['success' => true];
            } else {
                return ['success' => false, 'message' => 'Item not found'];
            }
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Failed to update item'];
        }
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
            })
            ->with(['listItems' => function ($query) {
                $query->where('user_list_id', $this->userList->id);
            }]);
            $searchResults = $query->paginate(10)->withQueryString();
        }

        // Get list items with server-side filtering and sorting
        $query = $this->userList->listItems()
            ->with(['pluCode'])
            ->join('plu_codes', 'list_items.plu_code_id', '=', 'plu_codes.id');
        
        // Apply filters if selected
        if (!empty($this->selectedCategory)) {
            $query->where('plu_codes.category', $this->selectedCategory);
        }
        
        if (!empty($this->selectedCommodity)) {
            $query->where('plu_codes.commodity', $this->selectedCommodity);
        }
        
        $listItems = $query
            ->orderBy('plu_codes.commodity', 'asc') // Group by commodity first
            ->orderBy('list_items.organic', 'asc') // Within commodity: regular first, then organic
            ->orderByRaw('CAST(plu_codes.plu AS UNSIGNED) ASC') // Within organic status: PLU code ascending
            ->select('list_items.*')
            ->get()
            ->load('pluCode'); // Ensure pluCode relationship is loaded
        
        // Create a map of PLU codes that have both regular and organic versions
        $dualVersionPluCodes = $listItems->groupBy('plu_code_id')
            ->filter(function ($items) {
                return $items->where('organic', true)->isNotEmpty() && 
                       $items->where('organic', false)->isNotEmpty();
            })
            ->keys();

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
            'dualVersionPluCodes' => $dualVersionPluCodes,
        ]);
    }
}
