<?php

namespace App\Livewire;

use App\Models\PLUCode;
use Livewire\Component;

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
     * Indicates whether this item is organic.
     *
     * @var bool
     */
    public $isOrganic = false;

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
     * @param  int  $pluCodeId
     * @param  bool  $isOrganic
     * @return void
     */
    public function openModal($pluCodeId, $isOrganic = false)
    {
        $this->pluCode = PLUCode::find($pluCodeId);
        $this->isOrganic = $isOrganic;

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
        $this->isOrganic = false;
    }

    /**
     * Get the display PLU code (with organic prefix if applicable).
     *
     * @return string
     */
    public function getDisplayPluProperty()
    {
        if (! $this->pluCode) {
            return '';
        }

        return $this->isOrganic ? '9'.$this->pluCode->plu : $this->pluCode->plu;
    }

    /**
     * Get the barcode PLU code (with organic prefix if applicable).
     *
     * @return string
     */
    public function getBarcodePluProperty()
    {
        if (! $this->pluCode) {
            return '';
        }

        return $this->isOrganic ? '9'.$this->pluCode->plu : $this->pluCode->plu;
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
