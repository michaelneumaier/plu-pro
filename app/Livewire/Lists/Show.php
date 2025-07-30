<?php

namespace App\Livewire\Lists;

use App\Jobs\LookupUPCProduct;
use App\Models\ListItem;
use App\Models\PLUCode;
use App\Models\UPCCode;
use App\Models\UserList;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

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

    // UPC-related properties
    public $upcResults = [];
    public $upcLookupInProgress = false;
    public $upcError = null;
    public $showCommodityModal = false;
    public $pendingUpcItem = null;
    public $selectedUpcCategory = '';
    public $selectedUpcCommodity = '';

    protected $listeners = [
        'retry-add-plu' => 'retryAddPlu',
        'list-item-updated' => 'handleListItemUpdated',
        'upc-ready-for-list' => 'handleUPCReadyForList',
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
        
        // Check if initial search term is a UPC and trigger lookup
        if (!empty(trim($this->searchTerm)) && $this->isUPCFormat($this->searchTerm)) {
            $this->searchUPC();
        }
    }

    protected function updatedUserList()
    {
        $this->initializeFilterOptions();
    }

    protected function initializeFilterOptions()
    {
        // For the commodity modal, we need ALL PLU commodities, not just those in the current list
        $this->commodities = PLUCode::select('commodity')->distinct()->orderBy('commodity')->pluck('commodity')->toArray();
        
        // For filtering, get categories from list items (both PLU and UPC)
        $userList = $this->userList->listItems()->with(['pluCode', 'upcCode'])->get();

        // Update categories from both PLU and UPC items in the list
        $categories = collect();
        foreach ($userList as $item) {
            if ($item->item_type === 'plu' && $item->pluCode) {
                $categories->push($item->pluCode->category);
            } elseif ($item->item_type === 'upc' && $item->upcCode) {
                $categories->push($item->upcCode->category);
            }
        }
        
        $this->categories = $categories
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
        
        // Clear previous UPC results
        $this->upcResults = [];
        $this->upcLookupInProgress = false;
        $this->upcError = null;
        
        // If search term is empty, don't do anything
        if (empty(trim($this->searchTerm))) {
            return;
        }
        
        // Detect UPC format (12-13 digits)
        if ($this->isUPCFormat($this->searchTerm)) {
            $this->searchUPC();
        }
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

    /**
     * Check if search term is in UPC format
     */
    private function isUPCFormat(string $term): bool
    {
        return preg_match('/^\d{12,13}$/', trim($term));
    }

    /**
     * Search for UPC in database or trigger API lookup
     */
    private function searchUPC(): void
    {
        // First check if UPC already exists in our database
        $cachedUPC = UPCCode::where('upc', $this->searchTerm)->first();
        
        if ($cachedUPC) {
            $this->upcResults = [$cachedUPC];
            $this->upcLookupInProgress = false;
        } else {
            // Trigger API lookup for new UPC
            $this->upcLookupInProgress = true;
            LookupUPCProduct::dispatch($this->searchTerm, auth()->id());
            
            // Start polling to check if UPC was found
            $this->dispatch('start-upc-polling');
        }
    }

    /**
     * Check for UPC lookup results (called via polling)
     */
    public function checkUPCResults()
    {
        if ($this->upcLookupInProgress && $this->isUPCFormat($this->searchTerm)) {
            // First check if the lookup failed
            $failureInfo = Cache::get("upc_lookup_failed_{$this->searchTerm}");
            
            if ($failureInfo) {
                $this->upcLookupInProgress = false;
                $this->upcError = $failureInfo['message'] ?? 'Product not found';
                $this->dispatch('stop-upc-polling');
                
                // Clear the cache entry
                Cache::forget("upc_lookup_failed_{$this->searchTerm}");
                return;
            }
            
            // Check if UPC was found
            $foundUPC = UPCCode::where('upc', $this->searchTerm)->first();
            
            if ($foundUPC) {
                $this->upcResults = [$foundUPC];
                $this->upcLookupInProgress = false;
                $this->upcError = null;
                $this->dispatch('stop-upc-polling');
            }
        }
    }

    /**
     * Initiate adding UPC to list - show commodity selection modal
     */
    public function addUPCToList($upcCodeId)
    {
        $this->pendingUpcItem = UPCCode::find($upcCodeId);
        $this->selectedUpcCategory = $this->pendingUpcItem->category ?? '';
        $this->selectedUpcCommodity = $this->pendingUpcItem->commodity ?? '';
        $this->showCommodityModal = true;
    }

    /**
     * Confirm UPC addition with selected category/commodity
     */
    public function confirmUPCAddition()
    {
        $this->validate([
            'selectedUpcCategory' => 'required|in:Fruits,Vegetables,Herbs,Nuts,Dried Fruits,Retailer Assigned Numbers',
            'selectedUpcCommodity' => 'required',
        ]);

        // Update UPC with selected category/commodity
        $this->pendingUpcItem->update([
            'category' => $this->selectedUpcCategory,
            'commodity' => $this->selectedUpcCommodity,
        ]);

        // Add UPC to list
        $this->addUPCCodeToList($this->pendingUpcItem->id);

        $this->resetCommodityModal();
    }

    /**
     * Cancel UPC commodity selection
     */
    public function cancelUPCAddition()
    {
        $this->resetCommodityModal();
    }

    /**
     * Reset commodity selection modal
     */
    private function resetCommodityModal()
    {
        $this->showCommodityModal = false;
        $this->pendingUpcItem = null;
        $this->selectedUpcCategory = '';
        $this->selectedUpcCommodity = '';
    }

    /**
     * Handle UPC ready for list event from SearchPLUCode component
     */
    public function handleUPCReadyForList($data)
    {
        $this->addUPCCodeToList($data['upcCodeId']);
    }

    /**
     * Add UPC code to the current list
     */
    public function addUPCCodeToList($upcCodeId)
    {
        // Check if this UPC already exists in the list
        $exists = $this->userList->listItems()
            ->where('upc_code_id', $upcCodeId)
            ->where('item_type', 'upc')
            ->exists();

        if (!$exists) {
            DB::transaction(function () use ($upcCodeId) {
                $listItem = $this->userList->listItems()->create([
                    'upc_code_id' => $upcCodeId,
                    'item_type' => 'upc',
                    'inventory_level' => 0.0,
                    'organic' => false, // UPC items don't have organic variants
                ]);

                // Load the UPC code data for the new item
                $listItem->load('upcCode');

                // Update refresh token to reset wire:key values and prevent snapshot errors
                $this->refreshToken = time();

                // Dispatch browser event for Alpine.js components to catch
                $this->js("
                    window.dispatchEvent(new CustomEvent('item-added-to-list', { 
                        detail: { 
                            upcCodeId: {$upcCodeId}, 
                            itemType: 'upc'
                        } 
                    }));
                ");

                session()->flash('message', 'UPC item added successfully!');

                // Update the relationships for subsequent renders
                $this->userList->load(['listItems.pluCode', 'listItems.upcCode']);
                $this->initializeFilterOptions();

                // Notify any listening carousel components that items have changed
                $this->dispatch('list-items-updated');
            });
        } else {
            session()->flash('message', 'This UPC item already exists in your list');
        }
    }

    public function addPLUCodeSilent($pluCodeId, $organic = false)
    {
        // This method adds items without triggering component re-render
        // Check if this specific version (regular or organic) already exists
        $exists = $this->userList->listItems()
            ->where('plu_code_id', $pluCodeId)
            ->where('organic', $organic)
            ->exists();

        if (! $exists) {
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

            // Dispatch browser event for Alpine.js components to catch
            $this->js("
                window.dispatchEvent(new CustomEvent('item-added-to-list', { 
                    detail: { 
                        pluCodeId: {$pluCodeId}, 
                        organic: ".($organic ? 'true' : 'false').' 
                    } 
                }));
            ');

            // Return success without modifying component properties
            return ['success' => true, 'listItem' => $listItem];
        }

        return ['success' => false, 'message' => 'This '.($organic ? 'organic' : 'regular').' item already exists in your list'];
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

    public function handleListItemUpdated()
    {
        // Handle individual organic toggle updates
        $this->refreshToken = time();
        $this->userList->load(['listItems.pluCode']);
    }

    public function updateListName($newName)
    {
        $trimmedName = trim($newName);

        if (! empty($trimmedName) && $trimmedName !== $this->userList->name) {
            $this->userList->update(['name' => $trimmedName]);
            session()->flash('message', 'List name updated successfully!');
        }
    }

    public function refreshListAfterEdit()
    {
        // Update refresh token to force component re-render
        $this->refreshToken = time();

        // Reload list items with latest organic status from database
        $this->userList->load(['listItems.pluCode']);

        // Update filter options in case organic status affected categories/commodities
        $this->initializeFilterOptions();
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

    // Share functionality
    public $showShareModal = false;

    public $isPublic;

    public $shareUrl = '';

    // Publish to marketplace functionality
    public $showPublishModal = false;

    public $marketplaceTitle = '';

    public $marketplaceDescription = '';

    public $marketplaceCategory = '';

    // Unpublish functionality
    public $showUnpublishModal = false;

    public function toggleShareModal()
    {
        $this->showShareModal = ! $this->showShareModal;
        // Update the reactive properties when opening modal
        $this->isPublic = $this->userList->is_public;
        $this->shareUrl = $this->userList->share_url ?? '';
    }

    public function togglePublicSharing()
    {
        $this->userList->update([
            'is_public' => ! $this->userList->is_public,
        ]);

        if (! $this->userList->share_code) {
            $this->userList->generateNewShareCode();
        }

        $this->userList->refresh();

        // Update reactive properties
        $this->isPublic = $this->userList->is_public;
        $this->shareUrl = $this->userList->share_url ?? '';
    }

    public function togglePublishModal()
    {
        $this->showPublishModal = ! $this->showPublishModal;

        if ($this->showPublishModal) {
            $this->marketplaceTitle = $this->userList->name;
            $this->marketplaceDescription = '';
            $this->marketplaceCategory = '';
        } else {
            $this->resetPublishForm();
        }
    }

    public function publishToMarketplace()
    {
        $this->validate([
            'marketplaceTitle' => 'required|string|max:255',
            'marketplaceDescription' => 'nullable|string|max:1000',
            'marketplaceCategory' => 'nullable|string|max:50',
        ]);

        $this->userList->update([
            'marketplace_enabled' => true,
            'marketplace_title' => $this->marketplaceTitle,
            'marketplace_description' => $this->marketplaceDescription ?: null,
            'marketplace_category' => $this->marketplaceCategory ?: null,
            'published_at' => now(),
        ]);

        // Generate share code if it doesn't exist
        if (! $this->userList->share_code) {
            $this->userList->generateNewShareCode();
        }

        $this->togglePublishModal();
        session()->flash('message', 'List published to marketplace successfully!');
    }

    protected function resetPublishForm()
    {
        $this->marketplaceTitle = '';
        $this->marketplaceDescription = '';
        $this->marketplaceCategory = '';
    }

    public function confirmUnpublish()
    {
        $this->showUnpublishModal = true;
    }

    public function unpublishFromMarketplace()
    {
        $this->userList->update([
            'marketplace_enabled' => false,
            'marketplace_title' => null,
            'marketplace_description' => null,
            'marketplace_category' => null,
            'published_at' => null,
        ]);

        $this->showUnpublishModal = false;
        session()->flash('message', 'List unpublished from marketplace successfully!');
    }

    public function cancelUnpublish()
    {
        $this->showUnpublishModal = false;
    }

    public function render()
    {
        $query = PLUCode::query();
        $searchResults = collect();

        // Only search PLU codes for non-UPC terms
        if (strlen(trim($this->searchTerm ?? '')) >= 2 && !$this->isUPCFormat($this->searchTerm)) {
            $query->where(function ($q) {
                $q->where('plu', 'like', '%'.$this->searchTerm.'%')
                    ->orWhere('variety', 'like', '%'.$this->searchTerm.'%')
                    ->orWhere('commodity', 'like', '%'.$this->searchTerm.'%')
                    ->orWhere('aka', 'like', '%'.$this->searchTerm.'%');
            })
                ->with(['listItems' => function ($query) {
                    $query->where('user_list_id', $this->userList->id);
                }]);
            $searchResults = $query->paginate(10)->withQueryString();
        }

        // Get list items with server-side filtering and sorting
        $query = $this->userList->listItems()
            ->with(['pluCode', 'upcCode']);

        // Get all items first, then filter and sort in PHP to handle mixed PLU/UPC items
        $listItems = $query->get();

        // Apply filtering if selected
        if (!empty($this->selectedCategory)) {
            $listItems = $listItems->filter(function ($item) {
                return ($item->item_type === 'plu' && $item->pluCode->category === $this->selectedCategory) ||
                       ($item->item_type === 'upc' && $item->upcCode->category === $this->selectedCategory);
            });
        }

        if (!empty($this->selectedCommodity)) {
            $listItems = $listItems->filter(function ($item) {
                return ($item->item_type === 'plu' && $item->pluCode->commodity === $this->selectedCommodity) ||
                       ($item->item_type === 'upc' && $item->upcCode->commodity === $this->selectedCommodity);
            });
        }

        // Sort items with a single combined sort key: commodity + sort_priority + code
        // Order: Regular PLU, Organic PLU, UPC
        $listItems = $listItems->sortBy(function ($item) {
            $commodity = $item->item_type === 'plu' ? $item->pluCode->commodity : $item->upcCode->commodity;
            
            // Priority: Regular PLU (0), Organic PLU (1), UPC (2)
            if ($item->item_type === 'plu' && !$item->organic) {
                $priority = '0'; // Regular PLU first
            } elseif ($item->item_type === 'plu' && $item->organic) {
                $priority = '1'; // Organic PLU second
            } else {
                $priority = '2'; // UPC last
            }
            
            $code = $item->item_type === 'plu' ? 
                str_pad($item->pluCode->plu, 10, '0', STR_PAD_LEFT) : 
                $item->upcCode->name;
            
            return $commodity . '|' . $priority . '|' . $code;
        });

        // Create a map of PLU codes that have both regular and organic versions (only for PLU items)
        $dualVersionPluCodes = $listItems->where('item_type', 'plu')
            ->groupBy('plu_code_id')
            ->filter(function ($items) {
                return $items->where('organic', true)->isNotEmpty() &&
                       $items->where('organic', false)->isNotEmpty();
            })
            ->keys();

        // Prepare items data for JavaScript
        $allItemsData = $listItems->map(function ($item) {
            if ($item->item_type === 'plu') {
                return [
                    'id' => $item->id,
                    'item_type' => 'plu',
                    'plu_code_id' => $item->plu_code_id,
                    'plu' => $item->pluCode->plu,
                    'variety' => $item->pluCode->variety,
                    'commodity' => $item->pluCode->commodity,
                    'category' => $item->pluCode->category,
                    'organic' => $item->organic,
                    'inventory_level' => $item->inventory_level,
                    'size' => $item->pluCode->size,
                    'retail_price' => $item->pluCode->retail_price,
                    'consumer_usage_tier' => $item->pluCode->consumer_usage_tier,
                ];
            } else {
                return [
                    'id' => $item->id,
                    'item_type' => 'upc',
                    'upc_code_id' => $item->upc_code_id,
                    'upc' => $item->upcCode->upc,
                    'name' => $item->upcCode->name,
                    'commodity' => $item->upcCode->commodity,
                    'category' => $item->upcCode->category,
                    'organic' => false, // UPC items don't have organic variants
                    'inventory_level' => $item->inventory_level,
                    'brand' => $item->upcCode->brand,
                ];
            }
        })->values(); // Ensure it's a proper array, not an object

        return view('livewire.lists.show', [
            'listItems' => $listItems,
            'pluCodes' => $searchResults,
            'upcResults' => $this->upcResults,
            'upcLookupInProgress' => $this->upcLookupInProgress,
            'categories' => $this->categories,
            'commodities' => $this->commodities,
            'allItemsData' => $allItemsData,
            'dualVersionPluCodes' => $dualVersionPluCodes,
        ]);
    }
}
