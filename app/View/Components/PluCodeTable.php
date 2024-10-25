<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use App\Models\PLUCode;

class PluCodeTable extends Component
{
    public $collection;

    /**
     * The processed collection of PLU Codes.
     *
     * @var \Illuminate\Support\Collection
     */
    public $pluCodes;

    /**
     * Determine if the collection contains PLU Codes or List Items.
     *
     * @return void
     */

    /**
     * The method name to call when deleting a PLU Code.
     *
     * @var string|null
     */
    public $onDelete;

    /**
     * The method name to call when adding a PLU Code.
     *
     * @var string|null
     */
    public $onAdd;

    /**
     * Create a new component instance.
     *
     * @param \Illuminate\Support\Collection $collection
     * @param string|null $onDelete
     * @param string|null $onAdd
     * @return void
     */
    public function __construct($collection, $onDelete = null, $onAdd = null)
    {
        $this->collection = $collection;
        $this->pluCodes = $this->processCollection($collection);
        $this->onDelete = $onDelete;
        $this->onAdd = $onAdd;
    }

    /**
     * Process the incoming collection to ensure it's a collection of PLU Codes.
     *
     * @param \Illuminate\Support\Collection $collection
     * @return \Illuminate\Support\Collection
     */
    protected function processCollection($collection)
    {
        // Check if the first item is a PLUCode instance
        if ($collection->first() instanceof PLUCode) {
            return $collection;
        }

        // Otherwise, assume it's a collection of List Items with 'plu_id'
        // Extract PLU IDs
        $pluIds = $collection->pluck('plu_code_id')->unique();

        // Fetch PLU Codes based on IDs
        return PLUCode::whereIn('id', $pluIds)->get();
    }
    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.plu-code-table');
    }
}
