<!-- resources/views/livewire/plu-code-detail-modal.blade.php -->
<div>
    @if($isOpen && $pluCode)
    <div class="fixed inset-0 flex items-start justify-center bg-black bg-opacity-50 z-50"
        wire:keydown.escape.window="closeModal" aria-labelledby="pluCodeDetailTitle" role="dialog" aria-modal="true">
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

            <h2 id="pluCodeDetailTitle" class="text-2xl font-bold mb-4 text-gray-800 text-center">PLU Code Details</h2>

            <!-- Emphasized PLU Code -->
            <div class="text-center mb-4">
                <span class="text-4xl font-extrabold text-green-800">{{ $pluCode->plu }}</span> <!-- Dark green text -->
            </div>

            <!-- General Information Section -->
            <div class="bg-gray-50 p-4 rounded-lg shadow-sm mb-4">
                <h3 class="text-lg font-semibold mb-2">General Information</h3>
                <div class="flex justify-between">
                    <span class="text-gray-700">Variety:</span>
                    <span class="text-gray-900">{{ $pluCode->variety }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-700">Commodity:</span>
                    <span class="text-gray-900">{{ ucwords(strtolower($pluCode->commodity)) }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-700">Size:</span>
                    <span class="text-gray-900">{{ $pluCode->size }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-700">Botanical:</span>
                    <span class="text-gray-900">{{ $pluCode->botanical }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-700">Status:</span>
                    <span class="text-gray-900">{{ $pluCode->status }}</span>
                </div>
            </div>

            <!-- Additional Details Section -->
            <div class="bg-gray-50 p-4 rounded-lg shadow-sm mb-4">
                <h3 class="text-lg font-semibold mb-2">Additional Details</h3>
                @if(!empty($pluCode->aka))
                <div class="flex justify-between">
                    <span class="text-gray-700">AKA:</span>
                    <span class="text-gray-900">{{ $pluCode->aka }}</span>
                </div>
                @endif
                @if(!empty($pluCode->notes))
                <div class="flex justify-between">
                    <span class="text-gray-700">Notes:</span>
                    <span class="text-gray-900">{{ $pluCode->notes }}</span>
                </div>
                @endif
                <div class="flex justify-between">
                    <span class="text-gray-700">Created At:</span>
                    <span class="text-gray-900">{{ $pluCode->created_at->format('M d, Y') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-700">Updated At:</span>
                    <span class="text-gray-900">{{ $pluCode->updated_at->format('M d, Y') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-700">Link:</span>
                    <a href="{{ $pluCode->link }}" target="_blank" class="text-blue-600 hover:underline">{{
                        $pluCode->link }}</a>
                </div>
            </div>

            <!-- Barcode -->
            <div class="mt-6 flex justify-center h-10">
                <x-barcode code="{{ $pluCode->plu }}" />
            </div>
        </div>
    </div>
    @endif
</div>