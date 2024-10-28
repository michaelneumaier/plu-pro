<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use App\Models\PLUCode;

class PluCodeTable extends Component
{
    public $collection;

    public $selectedCategory;
    public $selectedCommodity;

    public $pluCodes;
    public $onDelete;
    public $onAdd;

    public function __construct($collection, $onDelete = null, $onAdd = null, $selectedCategory = null, $selectedCommodity = null)
    {
        $this->collection = $collection;
        $this->pluCodes = $this->processCollection($collection);
        $this->onDelete = $onDelete;
        $this->onAdd = $onAdd;
        $this->selectedCategory = $selectedCategory;
        $this->selectedCommodity = $selectedCommodity;
    }

    protected function processCollection($collection)
    {
        // Check if the first item is a PLUCode instance
        if ($collection->first() instanceof PLUCode) {
            return $collection;
        }

        // Otherwise, assume it's a collection of List Items with 'plu_id'
        // Extract PLU IDs
        $pluIds = $collection->pluck('plu_code_id')->unique();

        $collection = PLUCode::whereIn('id', $pluIds)->get();
        if ($this->selectedCommodity) {
            $collection->where('commodity', $this->selectedCommodity);
        }

        // Apply category filter if selected
        if ($this->selectedCategory) {
            $collection->where('category', $this->selectedCategory);
        }
        // Fetch PLU Codes based on IDs
        return $collection;
    }
    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.plu-code-table');
    }
}
