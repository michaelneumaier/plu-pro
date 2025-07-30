<?php

namespace App\Livewire; // Corrected namespace from App\Livewire to App\Http\Livewire

use App\Events\UPCLookupCompleted;
use App\Events\UPCLookupFailed;
use App\Jobs\LookupUPCProduct;
use App\Models\PLUCode;
use App\Models\UPCCode;
use Livewire\Component;
use Livewire\WithPagination;

class SearchPLUCode extends Component
{
    use WithPagination;

    // Search and filter properties
    public $searchTerm = '';

    public $selectedCommodity = '';

    public $selectedCategory = '';

    // Available filter options
    public $commodities = [];

    public $categories = [];

    // Toggle for filters visibility
    public $showFilters = false;

    // Sorting properties
    public $sortOption = 'plu_asc'; // Default sort option

    // UPC-related properties
    public $upcResults = [];
    public $upcLookupInProgress = false;
    public $showCommodityModal = false;
    public $pendingUpcItem = null;
    public $selectedUpcCategory = '';
    public $selectedUpcCommodity = '';

    // Define query string parameters for persistence (optional)
    protected $queryString = [
        'searchTerm' => ['except' => ''],
        'selectedCommodity' => ['except' => ''],
        'selectedCategory' => ['except' => ''],
        'sortOption' => ['except' => 'plu_asc'],
    ];

    // Listeners for events from child components
    protected $listeners = [
        'filtersUpdated' => 'handleFiltersUpdated',
        'setSortOption' => 'setSortOption',
    ];

    /**
     * Initialize component with available filter options.
     */
    public function mount()
    {
        $this->initializeFilterOptions();
    }

    /**
     * Reset to the first page when search term or filters are updated.
     * Also detect UPC format and trigger lookup if needed.
     */
    public function updatingSearchTerm()
    {
        $this->resetPage();
        
        // Clear previous UPC results
        $this->upcResults = [];
        $this->upcLookupInProgress = false;
        
        // Detect UPC format (12-13 digits)
        if ($this->isUPCFormat($this->searchTerm)) {
            $this->searchUPC();
        }
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
            $foundUPC = UPCCode::where('upc', $this->searchTerm)->first();
            
            if ($foundUPC) {
                $this->upcResults = [$foundUPC];
                $this->upcLookupInProgress = false;
                $this->dispatch('stop-upc-polling');
            }
        }
    }

    public function updatingSelectedCommodity()
    {
        $this->resetPage();
    }

    public function updatingSelectedCategory()
    {
        $this->resetPage();
    }

    /**
     * Initialize filter options based on the PLUCode model.
     */
    protected function initializeFilterOptions()
    {
        $this->commodities = PLUCode::select('commodity')->distinct()->orderBy('commodity')->pluck('commodity')->toArray();
        $this->categories = PLUCode::select('category')->distinct()->orderBy('category')->pluck('category')->toArray();
    }

    /**
     * Toggle the visibility of filters.
     */
    public function toggleFilters()
    {
        $this->showFilters = ! $this->showFilters;
    }

    /**
     * Reset all filters to their default states.
     */
    public function resetFilters()
    {
        $this->reset(['searchTerm', 'selectedCommodity', 'selectedCategory']);
        $this->resetPage();
        $this->showFilters = false;
    }

    /**
     * Handle filters updated from the FilterSection component.
     *
     * @param  array  $filters
     */
    public function handleFiltersUpdated($filters)
    {
        $this->selectedCategory = $filters['selectedCategory'];
        $this->selectedCommodity = $filters['selectedCommodity'];
        $this->resetPage();
    }

    public function setSortOption($option)
    {
        $this->sortOption = $option;
        $this->resetPage();
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

        // Emit event to add UPC to list (handled by parent component)
        $this->dispatch('upc-ready-for-list', [
            'upcCodeId' => $this->pendingUpcItem->id,
            'category' => $this->selectedUpcCategory,
            'commodity' => $this->selectedUpcCommodity,
        ]);

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
     * Parse the sortOption into field and direction.
     *
     * @return array
     */
    protected function parseSortOption()
    {
        // Use a regular expression to capture the field and direction
        preg_match('/^(.*)_(asc|desc)$/', $this->sortOption, $matches);

        // Set the field and direction based on the matches
        $field = $matches[1] ?? 'plu'; // Default to 'plu' if no match
        $direction = $matches[2] ?? 'asc'; // Default to 'asc' if no match

        // Validate field and direction
        $allowedFields = ['plu', 'consumer_usage_tier', 'created_at'];
        $allowedDirections = ['asc', 'desc'];

        if (! in_array($field, $allowedFields)) {
            $field = 'plu';
        }

        if (! in_array($direction, $allowedDirections)) {
            $direction = 'asc';
        }

        return [$field, $direction];
    }

    /**
     * Render the search input, filters, and PLU codes table.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        $query = PLUCode::query();

        // Apply search term if provided
        if ($this->searchTerm) {
            $query->where(function ($q) {
                $q->where('plu', 'like', '%'.$this->searchTerm.'%')
                    ->orWhere('variety', 'like', '%'.$this->searchTerm.'%')
                    ->orWhere('commodity', 'like', '%'.$this->searchTerm.'%')
                    ->orWhere('aka', 'like', '%'.$this->searchTerm.'%');
            });
        }

        // Apply commodity filter if selected
        if ($this->selectedCommodity) {
            $query->where('commodity', $this->selectedCommodity);
        }

        // Apply category filter if selected
        if ($this->selectedCategory) {
            $query->where('category', $this->selectedCategory);
        }

        // Apply sorting
        [$field, $direction] = $this->parseSortOption();

        // Custom sorting for consumer_usage_tier
        if ($field === 'consumer_usage_tier') {
            $query->orderByRaw("
                CASE consumer_usage_tier
                    WHEN 'Low' THEN 1
                    WHEN 'Medium' THEN 2
                    WHEN 'High' THEN 3
                    ELSE 4
                END ".($direction === 'desc' ? 'DESC' : 'ASC'));
        } else {
            $query->orderBy($field, $direction);
        }

        // Fetch paginated results
        $pluCodes = $query->paginate(25);

        return view('livewire.search-plu-code', [
            'pluCodes' => $pluCodes,
            'upcResults' => $this->upcResults,
            'upcLookupInProgress' => $this->upcLookupInProgress,
        ]);
    }
}
