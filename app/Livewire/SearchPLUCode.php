<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\PLUCode;
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

    // Define query string parameters for persistence (optional)
    protected $queryString = [
        'searchTerm' => ['except' => ''],
        'selectedCommodity' => ['except' => ''],
        'selectedCategory' => ['except' => ''],
    ];

    // Listeners for events from child components
    protected $listeners = [
        'filtersUpdated' => 'handleFiltersUpdated',
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
     */
    public function updatingSearchTerm()
    {
        $this->resetPage();
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
        $this->showFilters = !$this->showFilters;
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
     * @param array $filters
     */
    public function handleFiltersUpdated($filters)
    {
        $this->selectedCategory = $filters['selectedCategory'];
        $this->selectedCommodity = $filters['selectedCommodity'];
        $this->resetPage();
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
                $q->where('plu', 'like', '%' . $this->searchTerm . '%')
                    ->orWhere('variety', 'like', '%' . $this->searchTerm . '%')
                    ->orWhere('commodity', 'like', '%' . $this->searchTerm . '%')
                    ->orWhere('aka', 'like', '%' . $this->searchTerm . '%');
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

        // Fetch paginated results
        $pluCodes = $query->orderBy('plu')->paginate(10);

        return view('livewire.search-plu-code', [
            'pluCodes' => $pluCodes,
        ]);
    }
}
