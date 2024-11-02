<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use App\Models\PLUCode;
use App\Models\ListItem;

class PluCodeTable extends Component
{
    public $collection;

    public $selectedCategory;
    public $selectedCommodity;

    public $pluCodes;
    public $onDelete;
    public $onAdd;
    public $userListId;

    public function __construct($collection, $onDelete = null, $onAdd = null, $selectedCategory = null, $selectedCommodity = null, $userListId = null)
    {
        $this->collection = $collection;
        $this->userListId = $userListId;
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
            // Eager load the ListItems filtered by userListId
            $collection->load(['listItems' => function ($query) {
                $query->where('user_list_id', $this->userListId);
            }]);

            // Attach the specific ListItem to each PLUCode
            return $collection->map(function ($pluCode) {
                $pluCode->listItem = $pluCode->listItems->first();
                return $pluCode;
            });
        }

        // Otherwise, assume it's a collection of List Items with 'plu_code_id'
        // Extract PLU IDs
        $pluIds = $collection->pluck('plu_code_id')->unique();

        // Fetch PLUCodes with their corresponding ListItems for the userListId
        $pluCodes = PLUCode::whereIn('id', $pluIds)
            ->with(['listItems' => function ($query) {
                $query->where('user_list_id', $this->userListId);
            }])
            ->get();

        // Apply filters if any
        if ($this->selectedCommodity) {
            $pluCodes = $pluCodes->where('commodity', $this->selectedCommodity);
        }

        if ($this->selectedCategory) {
            $pluCodes = $pluCodes->where('category', $this->selectedCategory);
        }

        // Attach the specific ListItem to each PLUCode
        $pluCodes = $pluCodes->map(function ($pluCode) {
            $pluCode->listItem = $pluCode->listItems->first();
            return $pluCode;
        });

        return $pluCodes;
    }
    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.plu-code-table');
    }
}
