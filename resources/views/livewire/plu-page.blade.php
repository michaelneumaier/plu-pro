<div class="min-h-screen bg-gray-50">
    <!-- SEO Structured Data -->
    <script type="application/ld+json">
        @json($this->structuredData)
    </script>

    <!-- Hero Section -->
    <div class="bg-white border-b border-gray-200">
        <div class="max-w-4xl mx-auto px-4 py-8">
            <div class="text-center">
                @if($isOrganic)
                    <div class="mb-4">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            Organic
                        </span>
                    </div>
                @endif
                
                <h1 class="text-4xl font-bold text-gray-900 mb-4">
                    PLU Code {{ $displayPlu }}: {{ $isOrganic ? 'Organic ' : '' }}{{ $pluCode->variety }}
                </h1>
                
                <p class="text-lg text-gray-600 max-w-2xl mx-auto">
                    Complete information for {{ $isOrganic ? 'organic ' : '' }}PLU code {{ $displayPlu }}. 
                    Find barcode, commodity details, and everything you need to know about this produce code.
                </p>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-4xl mx-auto px-4 py-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- PLU Information -->
            <div class="lg:col-span-2 space-y-6">
                
                <!-- PLU Code Display -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">PLU Code Information</h2>
                    
                    <div class="text-center mb-6">
                        <div class="text-6xl font-bold {{ $isOrganic ? 'text-green-600' : 'text-blue-600' }} mb-2">
                            {{ $displayPlu }}
                        </div>
                        @if($isOrganic)
                            <p class="text-sm text-gray-600">Base PLU: {{ $basePlu }}</p>
                        @endif
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Variety</dt>
                            <dd class="text-lg text-gray-900">{{ $pluCode->variety }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Commodity</dt>
                            <dd class="text-lg text-gray-900">{{ ucwords(strtolower($pluCode->commodity)) }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Size</dt>
                            <dd class="text-lg text-gray-900">{{ $pluCode->size ?? 'Standard' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Type</dt>
                            <dd class="text-lg text-gray-900">{{ $isOrganic ? 'Organic' : 'Regular' }}</dd>
                        </div>
                        @if($pluCode->botanical)
                        <div class="col-span-2">
                            <dt class="text-sm font-medium text-gray-500">Botanical Name</dt>
                            <dd class="text-lg text-gray-900 italic">{{ $pluCode->botanical }}</dd>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Product Image -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Product Image</h3>
                    <div class="flex justify-center">
                        <x-plu-image :plu="$pluCode->plu" size="xl" class="shadow-lg rounded-lg" />
                    </div>
                </div>

                <!-- Barcode -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">
                        {{ $isOrganic ? 'Organic ' : '' }}Barcode
                    </h3>
                    <div class="text-center">
                        <div class="inline-block p-4 bg-gray-50 rounded-lg">
                            <x-barcode code="{{ $displayPlu }}" size="lg" />
                        </div>
                        <p class="text-sm text-gray-600 mt-2">UPC-A Barcode for PLU {{ $displayPlu }}</p>
                    </div>
                </div>

                <!-- Educational Content -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">About PLU Code {{ $displayPlu }}</h3>
                    
                    <div class="prose prose-sm max-w-none text-gray-700">
                        <p class="mb-4">
                            PLU code {{ $displayPlu }} identifies {{ $isOrganic ? 'organic ' : '' }}<strong>{{ $pluCode->variety }}</strong> 
                            in the {{ ucwords(strtolower($pluCode->commodity)) }} category. 
                            @if($isOrganic)
                                The "9" prefix indicates this is an organic variety, while the base code {{ $basePlu }} represents the regular version.
                            @else
                                This is a standard produce item. The organic version would be identified by PLU code 9{{ $basePlu }}.
                            @endif
                        </p>
                        
                        <h4 class="font-medium text-gray-900 mb-2">How to Use This PLU Code</h4>
                        <ul class="list-disc list-inside space-y-1 mb-4">
                            <li>At self-checkout, enter the PLU code {{ $displayPlu }} when prompted</li>
                            <li>For weight-based items, place the produce on the scale first</li>
                            <li>The system will automatically calculate the price based on current rates</li>
                        </ul>

                        @if($isOrganic)
                        <h4 class="font-medium text-gray-900 mb-2">Organic Certification</h4>
                        <p class="mb-4">
                            Items with PLU codes starting with "9" meet USDA organic standards, 
                            meaning they are grown without synthetic pesticides, fertilizers, or GMOs.
                        </p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                
                <!-- Quick Facts -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Quick Facts</h3>
                    <dl class="space-y-3">
                        <div>
                            <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">Status</dt>
                            <dd class="text-sm text-gray-900">{{ ucwords($pluCode->status ?? 'Active') }}</dd>
                        </div>
                        @if($pluCode->consumer_usage_tier)
                        <div>
                            <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">Usage</dt>
                            <dd class="text-sm text-gray-900">{{ ucwords($pluCode->consumer_usage_tier) }} Volume</dd>
                        </div>
                        @endif
                        <div>
                            <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">Type</dt>
                            <dd class="text-sm text-gray-900">{{ $isOrganic ? 'Organic Produce' : 'Conventional Produce' }}</dd>
                        </div>
                    </dl>
                </div>

                <!-- Alternative PLU -->
                @if(!$isOrganic)
                <div class="bg-green-50 rounded-lg border border-green-200 p-6">
                    <h3 class="text-lg font-medium text-green-900 mb-2">Organic Version</h3>
                    <p class="text-sm text-green-700 mb-3">
                        Looking for the organic version of this item?
                    </p>
                    <a href="/9{{ $basePlu }}" 
                       class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        View PLU 9{{ $basePlu }}
                    </a>
                </div>
                @else
                <div class="bg-blue-50 rounded-lg border border-blue-200 p-6">
                    <h3 class="text-lg font-medium text-blue-900 mb-2">Regular Version</h3>
                    <p class="text-sm text-blue-700 mb-3">
                        View the conventional version of this item:
                    </p>
                    <a href="/{{ $basePlu }}" 
                       class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
                        View PLU {{ $basePlu }}
                    </a>
                </div>
                @endif

                <!-- Related PLU Codes -->
                @if($relatedProducts->count() > 0)
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Related {{ ucwords(strtolower($pluCode->commodity)) }}</h3>
                    <div class="space-y-2">
                        @foreach($relatedProducts as $related)
                        <div class="flex justify-between items-center">
                            <div>
                                <a href="/{{ $related->plu }}" class="text-sm font-medium text-blue-600 hover:text-blue-800">
                                    PLU {{ $related->plu }}
                                </a>
                                <p class="text-xs text-gray-500">{{ $related->variety }}</p>
                            </div>
                            @if($isOrganic)
                                <a href="/9{{ $related->plu }}" class="text-xs text-green-600 hover:text-green-800">
                                    9{{ $related->plu }}
                                </a>
                            @endif
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
        </div>

        <!-- FAQ Section -->
        <div class="mt-12 bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-6">Frequently Asked Questions</h2>
            
            <div class="space-y-6">
                <div>
                    <h3 class="font-medium text-gray-900 mb-2">What does PLU {{ $displayPlu }} mean?</h3>
                    <p class="text-gray-700">
                        PLU {{ $displayPlu }} is the standardized code for {{ $isOrganic ? 'organic ' : '' }}{{ $pluCode->variety }}. 
                        PLU stands for "Price Look-Up" and helps cashiers and customers identify produce items quickly and accurately.
                    </p>
                </div>
                
                @if($isOrganic)
                <div>
                    <h3 class="font-medium text-gray-900 mb-2">Why does PLU {{ $displayPlu }} start with 9?</h3>
                    <p class="text-gray-700">
                        PLU codes starting with "9" indicate organic produce. The base code {{ $basePlu }} represents the conventional version, 
                        while 9{{ $basePlu }} identifies the same item grown under organic standards.
                    </p>
                </div>
                @endif
                
                <div>
                    <h3 class="font-medium text-gray-900 mb-2">Where can I use PLU {{ $displayPlu }}?</h3>
                    <p class="text-gray-700">
                        You can use this PLU code at most grocery stores, supermarkets, and farmers markets. 
                        It's especially helpful at self-checkout stations where you need to identify produce items.
                    </p>
                </div>
                
                <div>
                    <h3 class="font-medium text-gray-900 mb-2">Is PLU {{ $displayPlu }} available year-round?</h3>
                    <p class="text-gray-700">
                        Availability depends on the specific product and your location. Some produce items are seasonal, 
                        while others are available year-round through various growing regions and storage methods.
                    </p>
                </div>
            </div>
        </div>

        <!-- PLU Pro Features Section -->
        <div class="mt-12 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg border border-blue-200 p-8">
            <div class="text-center mb-8">
                <h2 class="text-2xl font-bold text-gray-900 mb-4">Manage Your PLU Codes with PLU Pro</h2>
                <p class="text-lg text-gray-600 max-w-3xl mx-auto">
                    Take your produce management to the next level with PLU Pro's comprehensive tools for creating, 
                    organizing, and sharing PLU code lists.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Create Custom Lists -->
                <div class="text-center">
                    <div class="inline-flex items-center justify-center w-12 h-12 bg-blue-100 rounded-lg mb-4">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Create Custom Lists</h3>
                    <p class="text-gray-600 text-sm">
                        Build personalized PLU code lists for your specific needs. Add PLU {{ $displayPlu }} and other codes 
                        to organize by season, supplier, or any category that works for your business.
                    </p>
                </div>

                <!-- Inventory Management -->
                <div class="text-center">
                    <div class="inline-flex items-center justify-center w-12 h-12 bg-green-100 rounded-lg mb-4">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Track Inventory Levels</h3>
                    <p class="text-gray-600 text-sm">
                        Monitor stock levels for each PLU code in your lists. Perfect for grocery managers, 
                        produce buyers, and anyone who needs to track {{ $isOrganic ? 'organic' : 'conventional' }} produce inventory.
                    </p>
                </div>

                <!-- Share & Collaborate -->
                <div class="text-center">
                    <div class="inline-flex items-center justify-center w-12 h-12 bg-purple-100 rounded-lg mb-4">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.367 2.684 3 3 0 00-5.367-2.684z"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Share Lists & Collaborate</h3>
                    <p class="text-gray-600 text-sm">
                        Share your PLU lists with team members, suppliers, or customers. Create public lists for 
                        seasonal items or publish to the marketplace for others to discover and copy.
                    </p>
                </div>
            </div>

            <div class="mt-8 bg-white rounded-lg border border-gray-200 p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h4 class="font-semibold text-gray-900 mb-3">Perfect for Produce Professionals</h4>
                        <ul class="space-y-2 text-sm text-gray-600">
                            <li class="flex items-start">
                                <svg class="w-4 h-4 text-green-500 mt-0.5 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Grocery store managers organizing seasonal displays
                            </li>
                            <li class="flex items-start">
                                <svg class="w-4 h-4 text-green-500 mt-0.5 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Produce buyers creating vendor-specific lists
                            </li>
                            <li class="flex items-start">
                                <svg class="w-4 h-4 text-green-500 mt-0.5 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Restaurant chains standardizing ingredient codes
                            </li>
                            <li class="flex items-start">
                                <svg class="w-4 h-4 text-green-500 mt-0.5 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Farmers market vendors tracking inventory
                            </li>
                        </ul>
                    </div>
                    <div>
                        <h4 class="font-semibold text-gray-900 mb-3">Key Features</h4>
                        <ul class="space-y-2 text-sm text-gray-600">
                            <li class="flex items-start">
                                <svg class="w-4 h-4 text-blue-500 mt-0.5 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                </svg>
                                Add both regular and organic versions of any PLU
                            </li>
                            <li class="flex items-start">
                                <svg class="w-4 h-4 text-blue-500 mt-0.5 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                </svg>
                                Real-time inventory tracking with mobile optimization
                            </li>
                            <li class="flex items-start">
                                <svg class="w-4 h-4 text-blue-500 mt-0.5 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                </svg>
                                Barcode generation for easy scanning workflows
                            </li>
                            <li class="flex items-start">
                                <svg class="w-4 h-4 text-blue-500 mt-0.5 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                </svg>
                                Offline-capable for use anywhere in your facility
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="text-center mt-8">
                @auth
                    <a href="{{ route('lists.index') }}" 
                       class="inline-flex items-center px-6 py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition-colors">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Add PLU {{ $displayPlu }} to Your Lists
                    </a>
                @else
                    <a href="{{ route('register') }}" 
                       class="inline-flex items-center px-6 py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition-colors mr-4">
                        Get Started with PLU Pro
                    </a>
                    <a href="{{ route('login') }}" 
                       class="inline-flex items-center px-6 py-3 border border-gray-300 text-gray-700 font-semibold rounded-lg hover:bg-gray-50 transition-colors">
                        Sign In
                    </a>
                @endauth
            </div>
        </div>
    </div>
</div>