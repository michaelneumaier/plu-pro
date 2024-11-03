<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;

class FilterSection extends Component
{
    // Public properties for filter options
    public $categories = [];
    public $commodities = [];

    // Selected filters
    #[Url]
    public $selectedCategory = '';

    #[Url]
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

    public function updatedSelectedCategory()
    {
        $this->dispatch('filtersUpdated', [
            'selectedCategory' => $this->selectedCategory,
            'selectedCommodity' => $this->selectedCommodity,
        ]);
        $this->dispatch('filter-changed');
    }

    public function updatedSelectedCommodity()
    {
        $this->dispatch('filtersUpdated', [
            'selectedCategory' => $this->selectedCategory,
            'selectedCommodity' => $this->selectedCommodity,
        ]);
        $this->dispatch('filter-changed');
    }

    /**
     * Reset all filters to their default states.
     */
    public function resetFilters()
    {
        $this->selectedCategory = '';
        $this->selectedCommodity = '';
        $this->dispatch('filtersUpdated', [
            'selectedCategory' => '',
            'selectedCommodity' => '',
        ]);
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
