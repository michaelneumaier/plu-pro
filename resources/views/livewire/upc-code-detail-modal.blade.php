<!-- resources/views/livewire/upc-code-detail-modal.blade.php -->
<div>
    @if($isOpen && $upcCode)
    <div class="fixed inset-0 flex items-start justify-center bg-black bg-opacity-50 z-50"
        wire:keydown.escape.window="closeModal" aria-labelledby="upcCodeDetailTitle" role="dialog" aria-modal="true">
        <div class="bg-white rounded-lg shadow-lg w-full md:w-3/4 lg:w-1/2 p-6 relative overflow-y-auto max-h-screen">
            <!-- Close Button -->
            <button wire:click="closeModal"
                class="absolute top-4 right-4 text-gray-500 hover:text-gray-700 focus:outline-none"
                aria-label="Close Modal">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>

            <h2 id="upcCodeDetailTitle" class="text-2xl font-bold mb-4 text-gray-800 text-center">UPC Code Details</h2>

            <!-- UPC Type Badge -->
            <div class="text-center mb-3">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                    </svg>
                    UPC Code
                </span>
            </div>

            <!-- Emphasized UPC Code -->
            <div class="text-center mb-4">
                <div class="flex flex-col items-center space-y-2">
                    <span class="text-4xl font-extrabold text-blue-800">{{ $this->displayUpc }}</span>
                    <div class="text-sm text-gray-600">Universal Product Code</div>
                </div>
            </div>

            <!-- Product Image -->
            <div class="flex justify-center mb-6">
                @if($upcCode->has_image)
                    <img src="{{ asset('storage/upc_images/' . $upcCode->upc . '.jpg') }}" 
                         alt="{{ $upcCode->name }}" 
                         class="w-48 h-48 object-cover rounded-lg shadow-lg">
                @else
                    <div class="w-48 h-48 bg-gray-200 rounded-lg shadow-lg flex items-center justify-center">
                        <div class="text-center">
                            <svg class="w-16 h-16 mx-auto text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            <span class="text-gray-500 text-sm">No Image Available</span>
                        </div>
                    </div>
                @endif
            </div>

            <!-- General Information Section -->
            <div class="bg-gray-50 p-4 rounded-lg shadow-sm mb-4">
                <h3 class="text-lg font-semibold mb-2">Product Information</h3>
                <div class="flex justify-between">
                    <span class="text-gray-700">Product Name:</span>
                    <span class="text-gray-900 text-right">{{ $upcCode->name }}</span>
                </div>
                @if(!empty($upcCode->description))
                <div class="flex justify-between">
                    <span class="text-gray-700">Description:</span>
                    <span class="text-gray-900 text-right">{{ $upcCode->description }}</span>
                </div>
                @endif
                @if(!empty($upcCode->brand))
                <div class="flex justify-between">
                    <span class="text-gray-700">Brand:</span>
                    <span class="text-gray-900">{{ $upcCode->brand }}</span>
                </div>
                @endif
                <div class="flex justify-between">
                    <span class="text-gray-700">Commodity:</span>
                    <span class="text-gray-900">{{ ucwords(strtolower($upcCode->commodity)) }}</span>
                </div>
                @if(!empty($upcCode->category))
                <div class="flex justify-between">
                    <span class="text-gray-700">Category:</span>
                    <span class="text-gray-900">{{ $upcCode->category }}</span>
                </div>
                @endif
            </div>

            <!-- API Data Section -->
            @if(!empty($upcCode->api_data))
            <div class="bg-gray-50 p-4 rounded-lg shadow-sm mb-4">
                <h3 class="text-lg font-semibold mb-2">Additional Details</h3>
                @if(isset($upcCode->api_data['categories']) && !empty($upcCode->api_data['categories']))
                <div class="flex justify-between">
                    <span class="text-gray-700">Kroger Categories:</span>
                    <span class="text-gray-900 text-right">{{ implode(', ', $upcCode->api_data['categories']) }}</span>
                </div>
                @endif
                <div class="flex justify-between">
                    <span class="text-gray-700">Added:</span>
                    <span class="text-gray-900">{{ $upcCode->created_at->format('M d, Y') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-700">Last Updated:</span>
                    <span class="text-gray-900">{{ $upcCode->updated_at->format('M d, Y') }}</span>
                </div>
                @if(!empty($upcCode->image_url))
                <div class="flex justify-between">
                    <span class="text-gray-700">Image Source:</span>
                    <a href="{{ $upcCode->image_url }}" target="_blank" class="text-blue-600 hover:underline text-sm">View Original</a>
                </div>
                @endif
            </div>
            @endif

            <!-- Barcode -->
            <div class="mt-6 flex flex-col items-center">
                <div class="text-sm text-gray-600 mb-2">
                    Barcode ({{ $this->barcodeUpc }})
                </div>
                <div class="h-10">
                    <x-barcode code="{{ $this->barcodeUpc }}" />
                </div>
            </div>
        </div>
    </div>
    @endif
</div>