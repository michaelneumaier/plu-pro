<?php

namespace App\Livewire;

use App\Models\PLUCode;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;

class PLUPage extends Component
{
    public $pluCode;

    public $isOrganic = false;

    public $requestedPlu;

    public $basePlu;

    public $displayPlu;

    public $seoData = [];

    public $relatedProducts = [];

    public function mount($plu)
    {
        $this->requestedPlu = $plu;

        // Determine if this is an organic PLU request (starts with 9 and is 5 digits)
        $this->isOrganic = str_starts_with($plu, '9') && strlen($plu) === 5;

        // Extract the base PLU (remove leading 9 for organic)
        $this->basePlu = $this->isOrganic ? substr($plu, 1) : $plu;

        // Set display PLU (what we show to user)
        $this->displayPlu = $this->isOrganic ? '9'.$this->basePlu : $this->basePlu;

        // Load PLU data from database using base PLU
        $this->loadPLUData();

        if (! $this->pluCode) {
            abort(404, 'PLU code not found');
        }

        // Generate SEO data and related products
        $this->generateSEOData();
        $this->loadRelatedProducts();
    }

    protected function loadPLUData()
    {
        $this->pluCode = Cache::remember("plu_data_{$this->basePlu}", 3600, function () {
            return PLUCode::where('plu', $this->basePlu)->first();
        });
    }

    protected function generateSEOData()
    {
        $variety = $this->pluCode->variety;
        $commodity = ucwords(strtolower($this->pluCode->commodity));
        $organicPrefix = $this->isOrganic ? 'Organic ' : '';
        $organicSuffix = $this->isOrganic ? ' - Organic' : '';

        $this->seoData = [
            'title' => "{$organicPrefix}PLU Code {$this->displayPlu}: {$variety}{$organicSuffix} - Price Lookup Code Information",
            'description' => "Complete information for {$organicPrefix}PLU code {$this->displayPlu} ({$organicPrefix}{$variety}). Find barcode, commodity details, and everything you need to know about this produce PLU code.",
            'keywords' => "PLU {$this->displayPlu}, PLU code {$this->displayPlu}, {$variety} PLU, {$commodity} PLU code, {$organicPrefix}produce codes",
            'canonical' => url("/{$this->displayPlu}"),
            'type' => $this->isOrganic ? 'organic' : 'regular',
        ];
    }

    protected function loadRelatedProducts()
    {
        $this->relatedProducts = Cache::remember("related_plus_{$this->pluCode->commodity}_{$this->basePlu}", 1800, function () {
            return PLUCode::where('commodity', $this->pluCode->commodity)
                ->where('plu', '!=', $this->basePlu)
                ->orderBy('plu')
                ->limit(8)
                ->get();
        });
    }

    public function getStructuredDataProperty()
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => ($this->isOrganic ? 'Organic ' : '').$this->pluCode->variety,
            'productID' => "PLU{$this->displayPlu}",
            'category' => ucwords(strtolower($this->pluCode->commodity)),
            'description' => $this->seoData['description'],
            'identifier' => [
                '@type' => 'PropertyValue',
                'name' => 'PLU Code',
                'value' => $this->displayPlu,
            ],
            'additionalProperty' => [
                [
                    '@type' => 'PropertyValue',
                    'name' => 'Organic',
                    'value' => $this->isOrganic ? 'Yes' : 'No',
                ],
                [
                    '@type' => 'PropertyValue',
                    'name' => 'Commodity',
                    'value' => ucwords(strtolower($this->pluCode->commodity)),
                ],
                [
                    '@type' => 'PropertyValue',
                    'name' => 'Size',
                    'value' => $this->pluCode->size ?? 'Standard',
                ],
            ],
        ];
    }

    public function render()
    {
        return view('livewire.plu-page')
            ->layout('layouts.app')
            ->title($this->seoData['title'])
            ->layoutData([
                'metaDescription' => $this->seoData['description'],
                'metaKeywords' => $this->seoData['keywords'],
                'canonical' => $this->seoData['canonical'],
                'structuredData' => $this->structuredData,
            ]);
    }
}
