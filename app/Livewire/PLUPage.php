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
        $variety = $this->pluCode->variety;
        $commodity = ucwords(strtolower($this->pluCode->commodity));
        $organicPrefix = $this->isOrganic ? 'Organic ' : '';
        $imageUrl = $this->getProductImageUrl();

        $product = [
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => $organicPrefix.$variety,
            'productID' => "PLU{$this->displayPlu}",
            'sku' => "PLU-{$this->displayPlu}",
            'url' => url("/{$this->displayPlu}"),
            'category' => $commodity,
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
                    'value' => $commodity,
                ],
                [
                    '@type' => 'PropertyValue',
                    'name' => 'Size',
                    'value' => $this->pluCode->size ?? 'Standard',
                ],
            ],
        ];

        if ($imageUrl) {
            $product['image'] = $imageUrl;
        }

        return $product;
    }

    public function getDefinedTermSchemaProperty()
    {
        $variety = $this->pluCode->variety;
        $commodity = ucwords(strtolower($this->pluCode->commodity));
        $organicPrefix = $this->isOrganic ? 'Organic ' : '';

        return [
            '@context' => 'https://schema.org',
            '@type' => 'DefinedTerm',
            'name' => "PLU {$this->displayPlu}",
            'description' => "PLU {$this->displayPlu} is the price look-up code for {$organicPrefix}{$variety}, a {$commodity} product.",
            'inDefinedTermSet' => [
                '@type' => 'DefinedTermSet',
                'name' => 'IFPS PLU Code System',
                'url' => 'https://www.ifpsglobal.com/plu-codes',
            ],
        ];
    }

    public function getFaqSchemaProperty()
    {
        $variety = $this->pluCode->variety;
        $commodity = ucwords(strtolower($this->pluCode->commodity));
        $organicPrefix = $this->isOrganic ? 'organic ' : '';

        $faqs = [
            [
                '@type' => 'Question',
                'name' => "What does PLU {$this->displayPlu} mean?",
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => "PLU {$this->displayPlu} is the standardized code for {$organicPrefix}{$variety}. PLU stands for \"Price Look-Up\" and helps cashiers and customers identify produce items quickly and accurately.",
                ],
            ],
            [
                '@type' => 'Question',
                'name' => "Where can I use PLU {$this->displayPlu}?",
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => "You can use this PLU code at most grocery stores, supermarkets, and farmers markets. It's especially helpful at self-checkout stations where you need to identify produce items.",
                ],
            ],
            [
                '@type' => 'Question',
                'name' => "Is PLU {$this->displayPlu} available year-round?",
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => 'Availability depends on the specific product and your location. Some produce items are seasonal, while others are available year-round through various growing regions and storage methods.',
                ],
            ],
        ];

        if ($this->isOrganic) {
            array_splice($faqs, 1, 0, [[
                '@type' => 'Question',
                'name' => "Why does PLU {$this->displayPlu} start with 9?",
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => "PLU codes starting with \"9\" indicate organic produce. The base code {$this->basePlu} represents the conventional version, while 9{$this->basePlu} identifies the same item grown under organic standards.",
                ],
            ]]);
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => $faqs,
        ];
    }

    public function getBreadcrumbSchemaProperty()
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                [
                    '@type' => 'ListItem',
                    'position' => 1,
                    'name' => 'Home',
                    'item' => url('/'),
                ],
                [
                    '@type' => 'ListItem',
                    'position' => 2,
                    'name' => 'PLU Directory',
                    'item' => url('/plu-directory'),
                ],
                [
                    '@type' => 'ListItem',
                    'position' => 3,
                    'name' => "PLU {$this->displayPlu}",
                    'item' => url("/{$this->displayPlu}"),
                ],
            ],
        ];
    }

    public function getProductImageUrl()
    {
        if ($this->pluCode->has_image) {
            return asset("storage/plu-images/{$this->pluCode->plu}.jpg");
        }

        return null;
    }

    public function getDirectAnswerProperty()
    {
        $variety = $this->pluCode->variety;
        $commodity = ucwords(strtolower($this->pluCode->commodity));
        $organicPrefix = $this->isOrganic ? 'organic ' : '';
        $digits = strlen($this->displayPlu);
        $organicNote = $this->isOrganic ? " The '9' prefix indicates organic certification." : '';

        return "PLU {$this->displayPlu} is the price look-up code for {$organicPrefix}{$variety}, a {$commodity} product. This {$digits}-digit code is used at grocery store checkouts to identify this produce item.{$organicNote} PLU codes are administered by the International Federation for Produce Standards (IFPS).";
    }

    public function render()
    {
        $layoutData = [
            'metaDescription' => $this->seoData['description'],
            'metaKeywords' => $this->seoData['keywords'],
            'canonical' => $this->seoData['canonical'],
            'ogType' => 'product',
        ];

        $imageUrl = $this->getProductImageUrl();
        if ($imageUrl) {
            $layoutData['ogImage'] = $imageUrl;
            $layoutData['twitterCard'] = 'summary_large_image';
        }

        return view('livewire.plu-page')
            ->layout('layouts.app')
            ->title($this->seoData['title'])
            ->layoutData($layoutData);
    }
}
