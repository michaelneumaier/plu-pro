<?php

namespace App\Livewire;

use App\Models\UPCCode;
use Livewire\Component;
use Livewire\Attributes\On;

class UpcCodeDetailModal extends Component
{
    /**
     * Indicates whether the modal is visible.
     *
     * @var bool
     */
    public $isOpen = false;

    /**
     * The UPC Code to display.
     *
     * @var \App\Models\UPCCode|null
     */
    public $upcCode = null;

    /**
     * Open the modal and set the UPC Code details.
     *
     * @param  int  $upcCodeId
     * @return void
     */
    #[On('upcCodeSelected')]
    public function openModal($upcCodeId = null)
    {
        $this->upcCode = UPCCode::find($upcCodeId);

        if ($this->upcCode) {
            $this->isOpen = true;
        } else {
            // Optionally, handle the case where the UPC Code isn't found
            session()->flash('error', 'UPC Code not found.');
        }
    }

    /**
     * Close the modal and reset the UPC Code.
     *
     * @return void
     */
    public function closeModal()
    {
        $this->isOpen = false;
        $this->upcCode = null;
    }

    /**
     * Get the display UPC code.
     *
     * @return string
     */
    public function getDisplayUpcProperty()
    {
        if (! $this->upcCode) {
            return '';
        }

        return $this->upcCode->upc;
    }

    /**
     * Get the barcode UPC code.
     *
     * @return string
     */
    public function getBarcodeUpcProperty()
    {
        if (! $this->upcCode) {
            return '';
        }

        return $this->upcCode->upc;
    }

    /**
     * Render the component view.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('livewire.upc-code-detail-modal');
    }
}