<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\PLUCode;

class PluCodeDetailModal extends Component
{
    /**
     * Indicates whether the modal is visible.
     *
     * @var bool
     */
    public $isOpen = false;

    /**
     * The PLU Code to display.
     *
     * @var \App\Models\PLUCode|null
     */
    public $pluCode = null;

    /**
     * Listeners for events.
     *
     * @var array
     */
    protected $listeners = [
        'pluCodeSelected' => 'openModal',
    ];

    /**
     * Open the modal and set the PLU Code details.
     *
     * @param int $pluCodeId
     * @return void
     */
    public function openModal($pluCodeId)
    {
        $this->pluCode = PLUCode::find($pluCodeId);

        if ($this->pluCode) {
            $this->isOpen = true;
        } else {
            // Optionally, handle the case where the PLU Code isn't found
            session()->flash('error', 'PLU Code not found.');
        }
    }

    /**
     * Close the modal and reset the PLU Code.
     *
     * @return void
     */
    public function closeModal()
    {
        $this->isOpen = false;
        $this->pluCode = null;
    }

    /**
     * Render the component view.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('livewire.plu-code-detail-modal');
    }
}
