<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\On;

class FilterSection extends Component
{
    // Public properties for filter options
    public $categories = [];
    public $commodities = [];

    // Selected filters
    public $selectedCategory = '';
    public $selectedCommodity = '';

    /**
     * Mount the component with filter options and initial selections.
     *
     * @param array $categories
     * @param array $commodities
     * @param string $selectedCategory
     * @param string $selectedCommodity
     */
    public function mount($categories, $commodities, $selectedCategory = '', $selectedCommodity = '')
    {
        $this->categories = $categories;
        $this->commodities = $commodities;
        $this->selectedCategory = $selectedCategory;
        $this->selectedCommodity = $selectedCommodity;
    }

    #[On('refreshFilters')]
    public function refreshFilters($categories, $commodities)
    {
        $this->categories = $categories;
        $this->commodities = $commodities;
    }

    public function updatedSelectedCategory()
    {
        $this->emitFiltersUpdated();
    }

    public function updatedSelectedCommodity()
    {
        $this->emitFiltersUpdated();
    }

    /**
     * Emit the 'filtersUpdated' event with current filter selections.
     */
    public function emitFiltersUpdated()
    {
        $this->dispatch('filtersUpdated', [
            'selectedCategory' => $this->selectedCategory,
            'selectedCommodity' => $this->selectedCommodity,
        ]);
    }

    /**
     * Reset all filters to their default states.
     */
    public function resetFilters()
    {
        $this->selectedCategory = '';
        $this->selectedCommodity = '';
        $this->emitFiltersUpdated();
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
