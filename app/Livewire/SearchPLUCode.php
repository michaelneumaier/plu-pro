<?php

namespace App\Livewire; // Corrected namespace from App\Livewire to App\Http\Livewire

use App\Events\UPCLookupCompleted;
use App\Events\UPCLookupFailed;
use App\Jobs\LookupUPCProduct;
use App\Models\PLUCode;
use App\Models\UPCCode;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
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
    public $upcError = null;
    public $showUpcModal = false;
    public $selectedUpcCode = null;

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
        
        // Check if initial search term is a UPC and trigger lookup
        if (!empty(trim($this->searchTerm)) && $this->isUPCFormat($this->searchTerm)) {
            $this->searchUPC();
        }
    }

    /**
     * Reset to the first page when search term or filters are updated.
     * Also detect UPC format and trigger lookup if needed.
     */
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

    /**
     * Check if search term is in UPC format
     */
    private function isUPCFormat(string $term): bool
    {
        $trimmed = trim($term);
        // Match 12-13 digit codes, or 13-digit codes starting with 0 (our formatted UPCs)
        return preg_match('/^\d{12,13}$/', $trimmed) || preg_match('/^0\d{12}$/', $trimmed);
    }

    private function isPLUFormat(string $term): bool
    {
        $trimmed = trim($term);
        // Match 4-5 digit PLU codes
        return preg_match('/^\d{4,5}$/', $trimmed);
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
     * View UPC details - open modal with product information
     */
    public function viewUPCDetails($upcCodeId)
    {
        $this->selectedUpcCode = UPCCode::find($upcCodeId);
        if ($this->selectedUpcCode) {
            $this->showUpcModal = true;
        }
    }

    /**
     * Close UPC details modal
     */
    public function closeUpcModal()
    {
        $this->showUpcModal = false;
        $this->selectedUpcCode = null;
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
    protected function getLayoutData()
    {
        return [
            'title' => 'Search PLU Codes',
        ];
    }

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
