<?php

namespace App\View\Components;

use Illuminate\Support\Facades\Storage;
use Illuminate\View\Component;

class PluImage extends Component
{
    public $plu;

    public $size;

    public $class;

    public $variety;

    public $commodity;

    public function __construct($plu, $size = 'sm', $class = '', $variety = '', $commodity = '')
    {
        $this->plu = $plu;
        $this->size = $size;
        $this->class = $class;
        $this->variety = $variety;
        $this->commodity = $commodity;
    }

    public function render()
    {
        $imagePath = $this->findImage();

        $altText = $this->variety
            ? "{$this->variety} - PLU {$this->plu}".($this->commodity ? " - {$this->commodity}" : '')
            : "PLU {$this->plu}";

        return view('components.plu-image', [
            'imagePath' => $imagePath,
            'sizeClasses' => $this->getSizeClasses(),
            'altText' => $altText,
        ]);
    }

    protected function findImage()
    {
        // Check for both jpg and png
        foreach (['jpg', 'png'] as $ext) {
            $path = "product_images/{$this->plu}.{$ext}";
            if (Storage::disk('public')->exists($path)) {
                return Storage::disk('public')->url($path);
            }
        }

        return null;
    }

    protected function getSizeClasses()
    {
        return match ($this->size) {
            'sm' => 'w-12 h-12',
            'md' => 'w-24 h-24',
            'lg' => 'w-48 h-48',
            'xl' => 'w-96 h-96',
            default => 'w-12 h-12'
        };
    }
}
