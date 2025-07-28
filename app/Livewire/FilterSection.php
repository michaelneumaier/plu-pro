<?php

namespace App\Livewire;

use Livewire\Attributes\Url;
use Livewire\Component;

class FilterSection extends Component
{
    // Public properties for filter options
    public $categories = [];

    public $commodities = [];

    // Selected filters - Let parent component handle URL state
    public $selectedCategory = '';

    public $selectedCommodity = '';

    // Track if we're in the middle of an update to prevent loops
    private $isUpdating = false;

    protected $listeners = [
        'refreshFilters' => 'handleRefreshFilters',
        'filter-state-updated' => 'syncWithParent',
    ];

    /**
     * Mount the component with filter options and initial selections.
     *
     * @param  array  $categories
     * @param  array  $commodities
     * @param  string  $selectedCategory
     * @param  string  $selectedCommodity
     */
    public function mount($categories, $commodities, $selectedCategory = '', $selectedCommodity = '')
    {
        $this->categories = $categories;
        $this->commodities = $commodities;

        // Normalize empty values to ensure consistency
        $this->selectedCategory = trim($selectedCategory ?? '') ?: '';
        $this->selectedCommodity = trim($selectedCommodity ?? '') ?: '';
    }

    /**
     * Hydrate method to sync state when component is refreshed
     */
    public function hydrate()
    {
        // Ensure values are always properly normalized
        $this->selectedCategory = trim($this->selectedCategory ?? '') ?: '';
        $this->selectedCommodity = trim($this->selectedCommodity ?? '') ?: '';
    }

    public function updatedSelectedCategory()
    {
        // Prevent infinite loops during programmatic updates
        if ($this->isUpdating) {
            return;
        }

        $this->dispatchFiltersUpdated();
    }

    public function updatedSelectedCommodity()
    {
        // Prevent infinite loops during programmatic updates
        if ($this->isUpdating) {
            return;
        }

        $this->dispatchFiltersUpdated();
    }

    /**
     * Dispatch filter updates to parent component
     */
    private function dispatchFiltersUpdated()
    {
        // Ensure values are normalized before dispatching
        $category = trim($this->selectedCategory ?? '') ?: '';
        $commodity = trim($this->selectedCommodity ?? '') ?: '';

        $this->dispatch('filtersUpdated', [
            'selectedCategory' => $category,
            'selectedCommodity' => $commodity,
        ]);
        $this->dispatch('filter-changed');
    }

    /**
     * Sync with parent component state updates
     */
    public function syncWithParent()
    {
        // Just ensure we're not in an updating state
        $this->isUpdating = false;
    }

    /**
     * Force update the filter state from parent
     */
    public function updateFromParent($selectedCategory, $selectedCommodity)
    {
        $this->isUpdating = true;

        $this->selectedCategory = trim($selectedCategory ?? '') ?: '';
        $this->selectedCommodity = trim($selectedCommodity ?? '') ?: '';

        $this->isUpdating = false;
    }

    /**
     * Reset all filters to their default states.
     */
    public function resetFilters()
    {
        $this->isUpdating = true;

        $this->selectedCategory = '';
        $this->selectedCommodity = '';

        $this->isUpdating = false;

        // Dispatch the reset
        $this->dispatchFiltersUpdated();
    }

    public function handleRefreshFilters($commodities, $categories)
    {
        $this->isUpdating = true;

        $this->commodities = $commodities;
        $this->categories = $categories;

        // If the currently selected category/commodity no longer exists in the new lists,
        // reset those selections
        $filtersChanged = false;

        if (! in_array($this->selectedCategory, $categories) && $this->selectedCategory !== '') {
            $this->selectedCategory = '';
            $filtersChanged = true;
        }

        if (! in_array($this->selectedCommodity, $commodities) && $this->selectedCommodity !== '') {
            $this->selectedCommodity = '';
            $filtersChanged = true;
        }

        $this->isUpdating = false;

        // Only dispatch if filters actually changed
        if ($filtersChanged) {
            $this->dispatchFiltersUpdated();
        }
    }

    /**
     * Render the component view.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('livewire.filter-section');
    }
}
