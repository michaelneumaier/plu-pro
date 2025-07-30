# UPC Integration Implementation Plan - UPDATED
## PLUPro - Adding UPC Support to Lists with Seamless Integration

### Executive Summary

This document outlines a comprehensive plan to integrate UPC (Universal Product Code) functionality into the existing PLUPro application through seamless integration with the current Add PLU codes component. Users will be able to add UPC items by simply typing 12-13 digit codes in the existing search interface, which will automatically detect UPC format and query the Kroger API. UPC items will appear in search results alongside PLU codes and display properly in the scan carousel with appropriate type indicators.

### Current System Analysis

#### Existing Architecture
- **Framework**: Laravel 11 with Livewire 3.0 for reactive components
- **Database**: SQLite with Eloquent ORM
- **Frontend**: Tailwind CSS + Alpine.js with offline-first PWA capabilities
- **Core Models**: PLUCode, UserList, ListItem with sophisticated relationships
- **Features**: Real-time inventory management, marketplace sharing, offline sync

#### Current List Functionality
- **Add PLU Interface**: Slide-up search panel with 300ms debounced real-time search
- **Search Logic**: Multi-field search across PLU, variety, commodity, and AKA fields (minimum 2 characters)
- **Results Display**: Paginated results (10 per page) with PLU badges, images, and dual Add Regular/Organic buttons
- **Inventory Management**: Decimal-based inventory levels with 0.5 increments using Alpine.js optimistic updates
- **Offline Support**: Comprehensive offline sync with conflict resolution and local storage
- **Display Logic**: Server-side filtering and sorting by commodity → organic status → PLU code
- **Scan Carousel**: Touch-enabled horizontal carousel showing items with inventory > 0, includes barcode generation

#### PLU Code Structure
- **Primary Data**: PLU code, commodity, variety, size, category
- **Metadata**: botanical name, AKA names, restrictions, notes
- **Images**: Kroger-sourced product images with queue-based downloading
- **Relationships**: Many-to-many with UserList through ListItem pivot table

#### Category & Commodity System
- **6 main categories**: Fruits (61 commodities), Vegetables (83), Herbs (32), Nuts (14), Dried Fruits (8), Retailer Assigned Numbers (2)
- **194 unique commodities** with some cross-category overlap (APPLES, PINEAPPLE, etc.)
- **Independent filtering**: Category and commodity filters work independently with AND logic
- **No hierarchical validation**: System allows selecting incompatible category/commodity combinations
- **Case-sensitive filtering** with exact matching using database LIKE queries
- **Dynamic loading** from database with distinct queries for filter dropdowns

### Requirements Analysis

#### Functional Requirements

**Seamless UPC Integration Process**:
1. User types in existing PLU search interface 
2. System detects 12-13 digit numeric input and debounces (300ms)
3. System automatically queries Kroger API for UPC product data
4. UPC results appear in search results alongside PLU results
5. User sees UPC items with "UPC" badge, product name, and description
6. User clicks "Add" button and selects appropriate commodity/category combination
7. System stores UPC item and adds to list
8. UPC appears in list display and scan carousel grouped by selected commodity

**Data Storage Requirements**:
- Store UPC codes to avoid duplicate API calls
- Cache product name, description, and image URL
- Associate UPC items with commodities for grouping
- Maintain inventory levels like PLU codes
- Support same offline sync patterns

**Integration Requirements**:
- Preserve all existing PLU functionality
- Maintain current list display and filtering logic
- Support same offline/online sync patterns
- Keep performance characteristics
- Ensure marketplace sharing includes UPC items

#### Non-Functional Requirements

**Performance**:
- API calls should not block user interface
- UPC data should cache locally to avoid repeated API calls
- List rendering performance should not degrade
- Offline functionality must work for UPC items

**User Experience**:
- UPC addition should feel natural within existing workflow
- Error handling for invalid UPCs or API failures
- Clear distinction between PLU and UPC items in interface
- Consistent inventory management experience

**Data Quality**:
- UPC validation to prevent invalid entries
- Commodity mapping to maintain list organization
- Image handling consistent with PLU approach
- Proper error states for failed API calls

### Kroger API Analysis

#### Authentication & Access
- **Credentials**: KROGER_CLIENT_ID and KROGER_CLIENT_SECRET (already configured)
- **API Type**: RESTful API returning JSON data
- **Authentication Method**: OAuth 2.0 client credentials flow with Bearer tokens
- **Base URL**: `https://api.kroger.com/v1/`
- **Token Endpoint**: `https://api.kroger.com/v1/connect/oauth2/token`
- **Token Expiration**: 1800 seconds (30 minutes)

#### Product Search Endpoint
- **URL**: `https://api.kroger.com/v1/products`
- **Method**: GET with Bearer token authorization
- **UPC Search Parameter**: `filter.term={upc_code}`
- **Required Scope**: `product.compact`

#### Actual API Response Structure
Based on confirmed Kroger API documentation:

```json
{
  "data": [{
    "productId": "0001111041600",
    "upc": "0001111041600",
    "aisleLocations": [
      {
        "bayNumber": "20",
        "description": "Dairy",
        "number": "100",
        "numberOfFacings": "6",
        "sequenceNumber": "4",
        "side": "L",
        "shelfNumber": "4",
        "shelfPositionInBay": "1"
      }
    ],
    "brand": "Kroger",
    "categories": [
      "Dairy"
    ],
    "description": "Kroger® 2% Reduced Fat Milk",
    "images": [
      {
        "perspective": "front",
        "size": "large",
        "url": "https://www.kroger.com/product/images/large/front/0001111041600"
      }
    ]
  }]
}
```

#### Rate Limiting & Usage
- Implement exponential backoff for API failures
- Cache successful responses to minimize API calls
- Consider batch processing for multiple UPC additions
- Implement proper error handling for rate limits

### Technical Implementation Plan - Seamless Integration Approach

#### Phase 1: Database Schema & Models

**1.1 Create UPC Storage Table**
```sql
CREATE TABLE upc_codes (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    upc VARCHAR(13) UNIQUE NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    brand VARCHAR(255),
    image_url VARCHAR(500),
    commodity VARCHAR(255) NOT NULL,
    api_data JSON,
    has_image BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    INDEX idx_upc (upc),
    INDEX idx_commodity (commodity)
);
```

**1.2 Update ListItem Table**
```sql
ALTER TABLE list_items ADD COLUMN item_type ENUM('plu', 'upc') DEFAULT 'plu';
ALTER TABLE list_items ADD COLUMN upc_code_id BIGINT NULL;
ALTER TABLE list_items ADD FOREIGN KEY (upc_code_id) REFERENCES upc_codes(id) ON DELETE CASCADE;
```

**1.3 Create UPCCode Model**
```php
class UPCCode extends Model
{
    protected $fillable = [
        'upc', 'name', 'description', 'brand', 
        'image_url', 'commodity', 'api_data', 'has_image'
    ];
    
    protected $casts = [
        'api_data' => 'array',
        'has_image' => 'boolean'
    ];
    
    public function listItems()
    {
        return $this->hasMany(ListItem::class);
    }
}
```

**1.4 Update ListItem Model**
```php
class ListItem extends Model
{
    protected $fillable = [
        'user_list_id', 'plu_code_id', 'upc_code_id',
        'inventory_level', 'organic', 'item_type'
    ];
    
    public function pluCode()
    {
        return $this->belongsTo(PLUCode::class);
    }
    
    public function upcCode()
    {
        return $this->belongsTo(UPCCode::class);
    }
    
    public function getItemAttribute()
    {
        return $this->item_type === 'plu' ? $this->pluCode : $this->upcCode;
    }
    
    public function getDisplayNameAttribute()
    {
        if ($this->item_type === 'plu') {
            return $this->pluCode->variety ?? $this->pluCode->commodity;
        }
        return $this->upcCode->name;
    }
}
```

#### Phase 2: Seamless Search Integration

**2.1 Update SearchPLUCode Component for UPC Detection**
```php
class SearchPLUCode extends Component
{
    public $searchTerm = '';
    public $upcResults = [];
    public $showCommodityModal = false;
    public $pendingUpcItem = null;
    
    protected $queryString = [
        'searchTerm' => ['except' => ''],
        // ... existing query string properties
    ];
    
    public function updatedSearchTerm()
    {
        $this->resetPage();
        
        // Detect UPC format (12-13 digits)
        if ($this->isUPCFormat($this->searchTerm)) {
            $this->searchUPC();
        } else {
            $this->upcResults = []; // Clear UPC results for non-UPC searches
        }
    }
    
    private function isUPCFormat(string $term): bool
    {
        return preg_match('/^\d{12,13}$/', trim($term));
    }
    
    private function searchUPC(): void
    {
        // Check cache first
        $cachedUPC = UPCCode::where('upc', $this->searchTerm)->first();
        
        if ($cachedUPC) {
            $this->upcResults = [$cachedUPC];
        } else {
            // Queue API lookup for new UPC
            LookupUPCProduct::dispatch($this->searchTerm, auth()->id())
                ->onQueue('upc-lookups');
            
            $this->upcResults = []; // Will be populated via Livewire events
        }
    }
    
    protected $listeners = [
        'upc-lookup-completed' => 'handleUPCLookupCompleted',
        'upc-lookup-failed' => 'handleUPCLookupFailed'
    ];
    
    public function handleUPCLookupCompleted($upcData)
    {
        if ($upcData['upc'] === $this->searchTerm) {
            $this->upcResults = [UPCCode::find($upcData['id'])];
        }
    }
    
    public function addUPCToList($upcCodeId)
    {
        $this->pendingUpcItem = UPCCode::find($upcCodeId);
        $this->showCommodityModal = true;
    }
}
```

**2.2 Create Kroger API Service**
```php
class KrogerApiService
{
    private $clientId;
    private $clientSecret;
    
    public function __construct()
    {
        $this->clientId = config('services.kroger.client_id');
        $this->clientSecret = config('services.kroger.client_secret');
    }
    
    public function getProductByUPC(string $upc): ?array
    {
        $accessToken = $this->getAccessToken();
        
        $response = Http::withToken($accessToken)
            ->get('https://api.kroger.com/v1/products', [
                'filter.term' => $upc
            ]);
            
        if ($response->successful()) {
            $data = $response->json();
            return $data['data'][0] ?? null;
        }
        
        throw new KrogerApiException($response->body());
    }
    
    private function getAccessToken(): string
    {
        return Cache::remember('kroger_access_token', 1700, function () {
            $credentials = base64_encode($this->clientId . ':' . $this->clientSecret);
            
            $response = Http::asForm()
                ->withHeaders(['Authorization' => 'Basic ' . $credentials])
                ->post('https://api.kroger.com/v1/connect/oauth2/token', [
                    'grant_type' => 'client_credentials',
                    'scope' => 'product.compact'
                ]);
            
            if ($response->successful()) {
                return $response->json()['access_token'];
            }
            
            throw new KrogerApiException('Failed to obtain access token');
        });
    }
}
```

**2.3 Create UPC Lookup Job**
```php
class LookupUPCProduct implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    public function __construct(
        private string $upc,
        private int $userId
    ) {}
    
    public function handle(KrogerApiService $krogerApi): void
    {
        try {
            $productData = $krogerApi->getProductByUPC($this->upc);
            
            if (!$productData) {
                throw new UPCNotFoundException("UPC {$this->upc} not found");
            }
            
            $upcCode = UPCCode::updateOrCreate(
                ['upc' => $this->upc],
                [
                    'name' => $productData['description'] ?? 'Unknown Product',
                    'description' => $productData['description'] ?? null,
                    'brand' => $productData['brand'] ?? null,
                    'image_url' => $productData['images'][0]['url'] ?? null,
                    'kroger_categories' => json_encode($productData['categories'] ?? []),
                    'api_data' => $productData
                ]
            );
            
            // Download image if available
            if ($upcCode->image_url) {
                DownloadUPCImage::dispatch($upcCode->id);
            }
            
            // Broadcast success via Livewire events
            event(new UPCLookupCompleted($this->userId, $upcCode));
            
        } catch (Exception $e) {
            Log::error("UPC lookup failed for {$this->upc}: " . $e->getMessage());
            event(new UPCLookupFailed($this->userId, $this->upc, $e->getMessage()));
        }
    }
}
```

#### Phase 3: Frontend Integration

**3.1 Update Search Results Template**
Update `resources/views/components/alpine-search-results.blade.php` to display UPC items alongside PLU items:

```blade
<!-- Existing PLU Results -->
@if(count($pluCodes) > 0)
    @foreach ($pluCodes as $pluCode)
        <!-- Existing PLU item template -->
        <div class="bg-white p-4 border-b border-gray-200">
            <!-- PLU Badge -->
            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                PLU
            </span>
            <!-- ... existing PLU display logic ... -->
        </div>
    @endforeach
@endif

<!-- New UPC Results Section -->
@if(count($this->upcResults) > 0)
    @foreach ($this->upcResults as $upcCode)
        <div class="bg-white p-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <!-- UPC Badge -->
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                        UPC
                    </span>
                    
                    <!-- Product Image -->
                    @if($upcCode->has_image)
                        <img src="{{ asset('storage/upc_images/' . $upcCode->upc . '.jpg') }}" 
                             alt="{{ $upcCode->name }}" 
                             class="w-12 h-12 object-cover rounded">
                    @else
                        <div class="w-12 h-12 bg-gray-200 rounded flex items-center justify-center">
                            <span class="text-gray-400 text-xs">No Image</span>
                        </div>
                    @endif
                    
                    <!-- Product Details -->
                    <div class="flex-1 min-w-0">
                        <h4 class="text-sm font-medium text-gray-900">{{ $upcCode->name }}</h4>
                        <p class="text-sm text-gray-500">
                            UPC: {{ $upcCode->upc }}
                            @if($upcCode->brand) • {{ $upcCode->brand }} @endif
                        </p>
                        @if($upcCode->description)
                            <p class="text-xs text-gray-400 mt-1">{{ Str::limit($upcCode->description, 60) }}</p>
                        @endif
                    </div>
                </div>
                
                <!-- Add Button -->
                <button wire:click="addUPCToList({{ $upcCode->id }})" 
                        class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                    Add to List
                </button>
            </div>
        </div>
    @endforeach
@endif
```

**3.2 Add Commodity Selection Modal**
```blade
<!-- Commodity Selection Modal for UPC Items -->
<x-dialog-modal wire:model="showCommodityModal">
    <x-slot name="title">Select Category & Commodity</x-slot>
    
    <x-slot name="content">
        @if($pendingUpcItem)
            <div class="mb-4">
                <h3 class="font-semibold text-lg">{{ $pendingUpcItem->name }}</h3>
                <p class="text-gray-600">{{ $pendingUpcItem->description }}</p>
                <p class="text-sm text-gray-500 mt-1">UPC: {{ $pendingUpcItem->upc }}</p>
            </div>
            
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <x-label for="selectedCategory" value="Category" />
                    <select wire:model="selectedCategory" 
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                        <option value="">Choose category...</option>
                        <option value="Fruits">Fruits</option>
                        <option value="Vegetables">Vegetables</option>
                        <option value="Herbs">Herbs</option>
                        <option value="Nuts">Nuts</option>
                        <option value="Dried Fruits">Dried Fruits</option>
                    </select>
                </div>
                
                <div>
                    <x-label for="selectedCommodity" value="Commodity" />
                    <select wire:model="selectedCommodity" 
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                        <option value="">Choose commodity...</option>
                        @foreach($availableCommodities as $commodity)
                            <option value="{{ $commodity }}">{{ $commodity }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        @endif
    </x-slot>
    
    <x-slot name="footer">
        <x-secondary-button wire:click="$set('showCommodityModal', false)">
            Cancel
        </x-secondary-button>
        
        <x-button wire:click="confirmUPCAddition" class="ml-2">
            Add to List
        </x-button>
    </x-slot>
</x-dialog-modal>
#### Phase 4: Scan Carousel Integration

**4.1 Update ItemCarousel Component**
```php
class ItemCarousel extends Component
{
    // ... existing properties ...
    
    public function fetchItems()
    {
        $userList = UserList::find($this->listId);
        
        $this->items = $userList->listItems()
            ->with(['pluCode', 'upcCode']) // Load both relationships
            ->where('list_items.inventory_level', '>', 0)
            ->get()
            ->map(function ($item) {
                // Add computed properties for unified display
                if ($item->item_type === 'plu') {
                    $item->display_code = $item->organic ? '9' . $item->pluCode->plu : $item->pluCode->plu;
                    $item->display_name = $item->pluCode->variety;
                    $item->display_commodity = $item->pluCode->commodity;
                    $item->display_category = $item->pluCode->category;
                } else {
                    $item->display_code = $item->upcCode->upc;
                    $item->display_name = $item->upcCode->name;
                    $item->display_commodity = $item->upcCode->commodity;
                    $item->display_category = $item->upcCode->category;
                }
                return $item;
            })
            ->sortBy([
                ['display_commodity', 'asc'],
                ['item_type', 'asc'], // PLU first, then UPC
                ['organic', 'asc'],   // Regular before organic for PLU
                ['display_code', 'asc']
            ])
            ->values();
    }
}
```

**4.2 Update Carousel Template**
Update `resources/views/livewire/item-carousel.blade.php`:

```blade
<!-- Product Information Section -->
<div class="p-4 space-y-3">
    <!-- Item Type Indicator -->
    <div class="text-center">
        @if($item->item_type === 'plu')
            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                PLU Code
                @if($item->organic) • Organic @endif
            </span>
        @else
            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                UPC Code
            </span>
        @endif
    </div>
    
    <!-- Product Name -->
    <h2 class="text-2xl font-bold text-gray-900 leading-tight text-center">
        {{ $item->display_name }}
    </h2>
    
    <!-- Category & Commodity -->
    <div class="text-center">
        <p class="text-sm text-gray-600">
            {{ $item->display_category }} • {{ $item->display_commodity }}
        </p>
    </div>
    
    <!-- Code Display -->
    <div class="bg-gray-50 rounded-lg p-3 text-center">
        <p class="text-sm text-gray-600 mb-1">
            {{ $item->item_type === 'plu' ? 'PLU Code' : 'UPC Code' }}
        </p>
        <p class="text-2xl font-mono font-bold text-gray-900">
            {{ $item->display_code }}
        </p>
    </div>
    
    <!-- Barcode Section -->
    <div class="bg-white border border-gray-200 rounded-lg p-2">
        <p class="text-xs text-gray-600 text-center mb-1">Barcode</p>
        <div class="flex justify-center items-center">
            <x-barcode code="{{ $item->display_code }}" size="md" />
        </div>
    </div>
    
    <!-- Product Image -->
    <div class="flex justify-center">
        @if($item->item_type === 'plu')
            <x-plu-image :plu="$item->pluCode->plu" size="lg" class="w-32 h-32 object-cover rounded-lg" />
        @else
            @if($item->upcCode->has_image)
                <img src="{{ asset('storage/upc_images/' . $item->upcCode->upc . '.jpg') }}" 
                     alt="{{ $item->display_name }}" 
                     class="w-32 h-32 object-cover rounded-lg">
            @else
                <div class="w-32 h-32 bg-gray-200 rounded-lg flex items-center justify-center">
                    <span class="text-gray-400 text-sm">No Image</span>
                </div>
            @endif
        @endif
    </div>
    
    <!-- Inventory Display -->
    <div class="text-center">
        <p class="text-lg font-semibold text-gray-900">
            Inventory: {{ $item->inventory_level }}
        </p>
    </div>
</div>
```

#### Phase 5: List Display Integration

**5.1 Update List Display Logic**
```php
// Update Lists/Show.php getGroupedListItemsProperty method
public function getGroupedListItemsProperty()
{
    $items = $this->list->listItems()
        ->with(['pluCode', 'upcCode'])
        ->get()
        ->groupBy(function ($item) {
            return $item->item_type === 'plu' 
                ? $item->pluCode->commodity 
                : $item->upcCode->commodity;
        })
        ->sortKeys();
    
    // Sort items within each commodity
    return $items->map(function ($commodityItems) {
        return $commodityItems->sortBy([
            ['item_type', 'asc'], // PLU first, then UPC
            ['organic', 'asc'],   // Regular before organic for PLU
            function ($item) {    // Then by code/name
                return $item->item_type === 'plu' 
                    ? $item->pluCode->plu 
                    : $item->upcCode->name;
            }
        ]);
    });
}
```

**5.2 Update List Item Display Template**
Update the list item display to show both PLU and UPC items with proper type indicators and unified inventory management:

```blade
<!-- Updated list item display component -->
<div class="flex items-center justify-between p-3 bg-white rounded-lg shadow">
    <div class="flex items-center space-x-3">
        <!-- Item Type Badge -->
        <div class="flex-shrink-0">
            @if ($item->item_type === 'plu')
                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                    PLU
                </span>
            @else
                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                    UPC
                </span>
            @endif
        </div>
        
        <!-- Item Image -->
        <div class="flex-shrink-0">
            @if ($item->item_type === 'plu' && $item->pluCode->has_image)
                <x-plu-image :plu="$item->pluCode->plu" size="sm" class="w-12 h-12 object-cover rounded" />
            @elseif ($item->item_type === 'upc' && $item->upcCode->has_image)
                <img src="{{ asset('storage/upc_images/' . $item->upcCode->upc . '.jpg') }}" 
                     alt="{{ $item->displayName }}" 
                     class="w-12 h-12 object-cover rounded">
            @else
                <div class="w-12 h-12 bg-gray-200 rounded flex items-center justify-center">
                    <span class="text-gray-400 text-xs">No Image</span>
                </div>
            @endif
        </div>
        
        <!-- Item Details -->
        <div class="flex-1 min-w-0">
            <h4 class="text-sm font-medium text-gray-900 truncate">
                {{ $item->displayName }}
            </h4>
            <p class="text-sm text-gray-500">
                @if ($item->item_type === 'plu')
                    PLU: {{ $item->pluCode->plu }}
                    @if ($item->organic) • Organic @endif
                @else
                    UPC: {{ $item->upcCode->upc }}
                    @if ($item->upcCode->brand) • {{ $item->upcCode->brand }} @endif
                @endif
            </p>
        </div>
    </div>
    
    <!-- Unified Inventory Controls -->
    <livewire:inventory-level 
        :list-item="$item" 
        :key="$item->id" />
</div>
```
        ->with(['pluCode', 'upcCode'])
        ->get()
        ->groupBy(function ($item) {
            return $item->item_type === 'plu' 
                ? $item->pluCode->commodity 
                : $item->upcCode->commodity;
        })
        ->sortKeys();
    
    // Sort items within each commodity
    return $items->map(function ($commodityItems) {
        return $commodityItems->sortBy([
            ['item_type', 'asc'], // PLU first, then UPC
            ['organic', 'asc'],   // Regular before organic
            function ($item) {    // Then by code/name
                return $item->item_type === 'plu' 
                    ? $item->pluCode->plu 
                    : $item->upcCode->name;
            }
        ]);
    });
}
```

#### Phase 4: User Interface Updates

**4.1 UPC Addition Modal**
```html
<!-- Add UPC Modal -->
<x-dialog-modal wire:model="showUPCModal">
    <x-slot name="title">Add UPC Product</x-slot>
    
    <x-slot name="content">
        @if (!$pendingUpcCode)
            <!-- UPC Input Stage -->
            <div class="mb-4">
                <x-label for="upc" value="UPC Code (12-13 digits)" />
                <x-input id="upc" type="text" wire:model="upcInput" 
                         placeholder="Enter UPC code" maxlength="13" />
                <x-input-error for="upcInput" class="mt-2" />
            </div>
        @else
            <!-- Commodity Selection Stage -->
            <div class="mb-4">
                <h3 class="font-semibold text-lg">{{ $pendingUpcCode->name }}</h3>
                <p class="text-gray-600">{{ $pendingUpcCode->description }}</p>
                
                @if ($pendingUpcCode->image_url)
                    <img src="{{ $pendingUpcCode->image_url }}" 
                         alt="{{ $pendingUpcCode->name }}" 
                         class="w-24 h-24 object-cover rounded mt-2">
                @endif
            </div>
            
            <div class="mb-4">
                <x-label for="commodity" value="Select Commodity Category" />
                <select wire:model="selectedCommodity" 
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                    <option value="">Choose a commodity...</option>
                    @foreach ($availableCommodities as $commodity)
                        <option value="{{ $commodity }}">{{ $commodity }}</option>
                    @endforeach
                </select>
                <x-input-error for="selectedCommodity" class="mt-2" />
            </div>
        @endif
    </x-slot>
    
    <x-slot name="footer">
        <x-secondary-button wire:click="$set('showUPCModal', false)">
            Cancel
        </x-secondary-button>
        
        @if (!$pendingUpcCode)
            <x-button wire:click="addUPC" class="ml-2">
                Lookup Product
            </x-button>
        @else
            <x-button wire:click="confirmUPCAddition" class="ml-2">
                Add to List
            </x-button>
        @endif
    </x-slot>
</x-dialog-modal>
```

**4.2 Updated List Item Display**
```html
<!-- List Item Component -->
<div class="flex items-center justify-between p-3 bg-white rounded-lg shadow">
    <div class="flex items-center space-x-3">
        <!-- Item Type Indicator -->
        <div class="flex-shrink-0">
            @if ($item->item_type === 'plu')
                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                    PLU
                </span>
            @else
                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                    UPC
                </span>
            @endif
        </div>
        
        <!-- Item Image -->
        <div class="flex-shrink-0">
            @if ($item->item_type === 'plu' && $item->pluCode->has_image)
                <img src="{{ asset('storage/product_images/' . $item->pluCode->plu . '.jpg') }}" 
                     alt="{{ $item->displayName }}" 
                     class="w-12 h-12 object-cover rounded">
            @elseif ($item->item_type === 'upc' && $item->upcCode->has_image)
                <img src="{{ asset('storage/upc_images/' . $item->upcCode->upc . '.jpg') }}" 
                     alt="{{ $item->displayName }}" 
                     class="w-12 h-12 object-cover rounded">
            @else
                <div class="w-12 h-12 bg-gray-200 rounded flex items-center justify-center">
                    <span class="text-gray-400 text-xs">No Image</span>
                </div>
            @endif
        </div>
        
        <!-- Item Details -->
        <div class="flex-1 min-w-0">
            <h4 class="text-sm font-medium text-gray-900 truncate">
                {{ $item->displayName }}
            </h4>
            <p class="text-sm text-gray-500">
                @if ($item->item_type === 'plu')
                    PLU: {{ $item->pluCode->plu }}
                    @if ($item->organic) • Organic @endif
                @else
                    UPC: {{ $item->upcCode->upc }}
                    @if ($item->upcCode->brand) • {{ $item->upcCode->brand }} @endif
                @endif
            </p>
        </div>
    </div>
    
    <!-- Inventory Controls -->
    <livewire:inventory-level 
        :list-item="$item" 
        :key="$item->id" />
</div>
```

#### Phase 5: Offline Support Integration

**5.1 Update Alpine.js Inventory Manager**
```javascript
// inventory-manager.js updates
updateValue(delta, itemType = 'plu') {
    this.localValue = Math.max(0, this.localValue + delta);
    this.pendingDelta += delta;
    
    // Store with item type context
    this.storeValue(itemType);
    
    // Debounced server sync
    clearTimeout(this.syncTimeout);
    this.syncTimeout = setTimeout(() => this.sync(itemType), 300);
}

sync(itemType = 'plu') {
    if (this.pendingDelta === 0) return;
    
    const endpoint = itemType === 'plu' 
        ? '/livewire/inventory-level'
        : '/livewire/upc-inventory-level';
    
    // ... rest of sync logic
}
```

**5.2 Create UPC Inventory Level Component**
```php
class UPCInventoryLevel extends Component
{
    public ListItem $listItem;
    public $inventoryLevel;
    
    public function mount()
    {
        $this->inventoryLevel = $this->listItem->inventory_level;
    }
    
    public function updateInventoryHeadless($delta, $clientTimestamp)
    {
        DB::transaction(function () use ($delta, $clientTimestamp) {
            $this->listItem->lockForUpdate();
            
            // Check for conflicts
            $lastUpdateTimestamp = $this->listItem->updated_at->timestamp * 1000;
            
            if ($lastUpdateTimestamp > $clientTimestamp) {
                return [
                    'success' => false,
                    'conflict' => true,
                    'serverValue' => (float) $this->listItem->inventory_level,
                    'serverTimestamp' => $lastUpdateTimestamp,
                ];
            }
            
            // Apply delta
            $newValue = max(0, $this->listItem->inventory_level + $delta);
            $this->listItem->update(['inventory_level' => $newValue]);
            
            return [
                'success' => true,
                'newValue' => (float) $newValue,
                'serverTimestamp' => now()->timestamp * 1000,
            ];
        });
    }
}
```

### Implementation Phases & Timeline - UPDATED FOR SEAMLESS INTEGRATION

#### Phase 1: Database Foundation (Week 1)
- [ ] Create UPC codes table migration with category and commodity fields
- [ ] Update ListItem table to support polymorphic relationships (item_type, upc_code_id)
- [ ] Implement UPCCode model with proper relationships and computed properties
- [ ] Update ListItem model for unified handling of PLU and UPC items
- [ ] Add database indexes for optimal query performance

#### Phase 2: Kroger API Integration (Week 1-2)
- [ ] Implement KrogerApiService with OAuth 2.0 client credentials flow
- [ ] Create LookupUPCProduct queue job with proper error handling
- [ ] Implement UPC image download job (similar to existing PLU image job)
- [ ] Add comprehensive API response caching (30-minute token management)
- [ ] Create Livewire events for real-time UPC lookup feedback

#### Phase 3: Seamless Search Integration (Week 2-3)
- [ ] Update SearchPLUCode component to detect 12-13 digit UPC input
- [ ] Implement automatic UPC API querying with 300ms debounce
- [ ] Update search results template to display UPC items alongside PLU items
- [ ] Add commodity/category selection modal for UPC items
- [ ] Implement unified search results with proper type indicators (PLU/UPC badges)

#### Phase 4: Scan Carousel Integration (Week 3)
- [ ] Update ItemCarousel component to handle both PLU and UPC items
- [ ] Add computed display properties (display_code, display_name, etc.)
- [ ] Update carousel template with item type indicators and UPC code display
- [ ] Ensure existing barcode component works properly with UPC codes
- [ ] Test touch navigation and haptic feedback with mixed item types

#### Phase 5: List Display & Inventory Integration (Week 3-4)
- [ ] Update list display queries to include UPC items with proper sorting
- [ ] Modify list item templates to show PLU/UPC type badges
- [ ] Ensure existing inventory management components work with UPC items
- [ ] Update filtering system to handle UPC items in commodity groups
- [ ] Test offline sync functionality with mixed PLU/UPC lists

#### Phase 6: Polish & Testing (Week 4-5)
- [ ] Comprehensive testing of seamless UPC addition workflow
- [ ] Performance testing with mixed PLU/UPC lists and search results
- [ ] Error handling for API failures, invalid UPCs, and network issues
- [ ] User acceptance testing for intuitive UPC addition experience
- [ ] Marketplace sharing functionality with UPC-containing lists

### Risk Analysis & Mitigation

#### Technical Risks

**API Reliability**:
- *Risk*: Kroger API downtime or rate limiting
- *Mitigation*: Implement robust caching, fallback to cached data, queue retry logic

**Database Performance**:
- *Risk*: Large lists with mixed item types may have performance issues
- *Mitigation*: Add database indexes, implement pagination, optimize queries

**Offline Sync Complexity**:
- *Risk*: Different sync patterns for PLU vs UPC items
- *Mitigation*: Unified sync interface, comprehensive testing of conflict resolution

#### Business Risks

**User Experience**:
- *Risk*: UPC addition workflow may be confusing
- *Mitigation*: User testing, clear UI states, helpful error messages

**Data Quality**:
- *Risk*: Incorrect commodity mapping for UPC items
- *Mitigation*: Commodity validation, user feedback loops, admin override capabilities

**API Costs**:
- *Risk*: High volume of API calls may incur costs
- *Mitigation*: Aggressive caching, batch processing, usage monitoring

### Requirements Clarifications - ADDRESSED

Based on your feedback, here are the confirmed requirements:

1. **✅ Kroger API Integration**: Using the official Kroger API with OAuth 2.0 client credentials flow at `https://api.kroger.com/v1/products`

2. **✅ UPC Format**: Supporting only 12-13 digit UPC codes (UPC-A format)

3. **✅ Integration Method**: Seamless integration into existing Add PLU codes component with automatic detection of 12-13 digit input

4. **✅ Category/Commodity Mapping**: UPC items must be assigned proper category AND commodity for filtering integration

5. **✅ Scan Carousel Display**: UPC items must appear in scan carousel with proper UPC code display and type indicators

6. **✅ No Fallback Commodity**: No generic fallback needed - users will select appropriate existing commodity

7. **✅ Image Storage**: UPC images stored similarly to PLU images in `storage/upc_images/`

8. **✅ Search Scope**: UPC search only within lists (not main search), but main search inclusion is acceptable if easier

9. **✅ Marketplace Sharing**: Lists with UPCs shareable exactly like PLU-only lists

10. **✅ Admin Features**: Filament admin for UPCs deferred until after initial implementation

11. **✅ Migration**: No migration concerns for existing lists - purely for new additions

### Additional Technical Considerations

**UPC Input Detection Strategy**:
- Monitor search input for 12-13 consecutive digits
- Trigger Kroger API lookup after 300ms debounce
- Display results alongside PLU results with clear type indicators

**Category/Commodity Assignment Flow**:
- Present modal after successful UPC lookup
- Require both category AND commodity selection
- Validate commodity belongs to selected category
- Store both fields for proper filtering integration

**Scan Carousel Requirements**:
- Display UPC code in monospace font like PLU codes
- Show "UPC Code" vs "PLU Code" type indicators  
- Use existing barcode component (already supports UPC format)
- Maintain consistent touch navigation and haptic feedback

### Success Metrics - UPDATED

**Seamless Integration Goals**:
- Users can add UPC items by simply typing 12-13 digits in existing search (no workflow change)
- UPC detection and API lookup happens automatically within 500ms of typing
- UPC results appear alongside PLU results with clear visual distinction
- UPC items display properly in scan carousel with correct code formatting
- Category/commodity selection completes in under 15 seconds

**Technical Performance**:
- Kroger API response time under 2 seconds for 95% of requests
- UPC lookup caching reduces duplicate API calls by 80%+
- List rendering performance unchanged with mixed PLU/UPC items
- Scan carousel navigation remains smooth with UPC items
- Offline sync maintains data integrity for UPC inventory changes

**User Experience**:
- Users intuitively discover UPC functionality without training
- Visual distinction between PLU and UPC items is clear and consistent
- Existing marketplace and sharing features work seamlessly with UPC lists
- Error states for invalid UPCs or API failures are helpful and recoverable

### Next Steps - IMPLEMENTATION READY

With your clarifications, this plan is ready for implementation. The recommended approach is:

1. **Week 1**: Start with database migrations and UPCCode model implementation
2. **Week 1-2**: Build Kroger API service and queue-based lookup system  
3. **Week 2-3**: Integrate UPC detection into existing SearchPLUCode component
4. **Week 3**: Update scan carousel for UPC display and test barcode generation
5. **Week 3-4**: Ensure list display and inventory management work with mixed items
6. **Week 4-5**: Polish, comprehensive testing, and marketplace integration

**Key Implementation Advantages**:
- Leverages existing, battle-tested components (search, inventory, carousel)
- Maintains current user workflows while adding new functionality
- Preserves offline-first architecture and performance characteristics
- Integrates naturally with filtering, sorting, and marketplace features

This approach provides maximum functionality with minimal disruption to your existing, well-architected system.