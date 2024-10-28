<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\PLUCode;

class PluCodeTable extends Component
{
    use WithPagination;

    public $collection;
    public $onDelete;
    public $onAdd;

    // Filter properties
    public $selectedCommodity = '';
    public $selectedType = '';

    // Available filter options
    public $commodities = [];
    public $types = [];

    // Initialize component with collection and action callbacks
    public function mount($collection, $onDelete = null, $onAdd = null)
    {
        $this->collection = $collection;
        $this->onDelete = $onDelete;
        $this->onAdd = $onAdd;

        $this->initializeFilters();
    }

    // Update filters whenever selectedCommodity or selectedType changes
    public function updatedSelectedCommodity()
    {
        $this->resetPage();
        $this->applyFilters();
    }

    public function updatedSelectedType()
    {
        $this->resetPage();
        $this->applyFilters();
    }

    // Initialize filter options based on the collection
    protected function initializeFilters()
    {
        $pluCodes = $this->getPluCodes($this->collection);

        $this->commodities = $pluCodes->pluck('commodity')->unique()->sort()->values()->toArray();
        $this->types = $pluCodes->pluck('type')->unique()->sort()->values()->toArray();
    }

    // Apply filters to the collection
    protected function applyFilters()
    {
        $filtered = $this->collection;

        if ($this->selectedCommodity) {
            $filtered = $filtered->filter(function ($plu) {
                return strtolower($plu->commodity) === strtolower($this->selectedCommodity);
            });
        }

        if ($this->selectedType) {
            $filtered = $filtered->filter(function ($plu) {
                return strtolower($plu->type) === strtolower($this->selectedType);
            });
        }

        $this->collection = $filtered;
        $this->initializeFilters();
    }

    // Process the incoming collection to ensure it's a collection of PLU Codes
    protected function getPluCodes($collection)
    {
        if ($collection->first() instanceof PLUCode) {
            return $collection;
        }

        // Assume it's a collection of List Items with 'plu_code_id'
        $pluIds = $collection->pluck('plu_code_id')->unique();
        return PLUCode::whereIn('id', $pluIds)->get()->paginate(10, ['*'], 'page', $this->page ?? 1);
    }

    public function render()
    {
        // Paginate the PLU codes
        $pluCodes = $this->collection;

        return view('livewire.plu-code-table', [
            'pluCodes' => $pluCodes,
        ]);
    }
}
